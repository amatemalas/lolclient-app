<?php

namespace App\Services\LeagueClient;

use Illuminate\Http\Client\Response;
use Throwable;

class LobbyProvider
{
    /**
     * Phases in which a lobby object can be fetched from the client.
     */
    private const LOBBY_PHASES = ['Lobby', 'Matchmaking', 'ReadyCheck', 'ChampSelect'];

    public function __construct(
        protected LeagueClientConnector $client,
        protected QueueProvider $queues,
    ) {}

    /**
     * Assemble the full lobby payload. Degrades gracefully to safe defaults
     * when the client is offline or a specific endpoint is down.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $connected = false;
        $error = null;
        $gameflow = 'None';
        $lobby = null;

        try {
            $phase = $this->client->request('GET', '/lol-gameflow/v1/gameflow-phase');
            $gameflow = is_string($phase) && $phase !== '' ? $phase : 'None';
            $connected = true;
        } catch (ClientNotRunningException $e) {
            $error = $e->getMessage();
        } catch (Throwable) {
            $error = 'The League client is not responding.';
        }

        if ($connected && in_array($gameflow, self::LOBBY_PHASES, true)) {
            $lobby = $this->normalizeLobby($this->bestEffort('/lol-lobby/v2/lobby'), $gameflow);
        }

        return compact('connected', 'error', 'gameflow', 'lobby');
    }

    /**
     * Switch the player into a lobby for the given queue, leaving the current
     * lobby first when one is already open.
     *
     * @return array{ok: bool, error?: string, lobby?: array<string, mixed>|null}
     */
    public function switchTo(int $queueId): array
    {
        if (! $this->queues->supports($queueId)) {
            return ['ok' => false, 'error' => 'Unsupported game mode.'];
        }

        $current = $this->bestEffort('/lol-lobby/v2/lobby');

        if (is_array($current)) {
            $left = $this->leave();

            if (! $left['ok']) {
                return $left;
            }
        }

        return $this->create($queueId);
    }

    /**
     * @return array{ok: bool, error?: string, lobby?: array<string, mixed>|null}
     */
    public function create(int $queueId): array
    {
        $response = $this->attempt('POST', '/lol-lobby/v2/lobby', ['queueId' => $queueId]);

        if (! $response['ok']) {
            return $response;
        }

        return [
            'ok' => true,
            'lobby' => $this->normalizeLobby($response['body'], 'Lobby'),
        ];
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function leave(): array
    {
        return $this->attempt('DELETE', '/lol-lobby/v2/lobby');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function startMatchmaking(): array
    {
        return $this->attempt('POST', '/lol-lobby/v2/lobby/matchmaking/search');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function stopMatchmaking(): array
    {
        return $this->attempt('DELETE', '/lol-lobby/v2/lobby/matchmaking/search');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function invite(int $summonerId): array
    {
        return $this->attempt('POST', '/lol-lobby/v2/lobby/members/'.$summonerId.'/grant-invite');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function kick(int $summonerId): array
    {
        return $this->attempt('POST', '/lol-lobby/v2/lobby/members/'.$summonerId.'/kick');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function setPosition(int $summonerId, string $position): array
    {
        $position = strtoupper(trim($position));

        if (! in_array($position, array_merge($this->queues->positions(), ['FILL']), true)) {
            return ['ok' => false, 'error' => 'Invalid position.'];
        }

        return $this->attempt('POST', '/lol-lobby/v2/lobby/members/'.$summonerId.'/position/'.$position);
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function acceptReadyCheck(): array
    {
        return $this->attempt('POST', '/lol-matchmaking/v1/ready-check/accept');
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public function declineReadyCheck(): array
    {
        return $this->attempt('POST', '/lol-matchmaking/v1/ready-check/decline');
    }

    /**
     * Normalize the raw LCU lobby object into the shape the views consume.
     *
     * @return array<string, mixed>|null
     */
    private function normalizeLobby(?array $raw, string $gameflow): ?array
    {
        if (! is_array($raw)) {
            return null;
        }

        $queueId = (int) ($raw['gameConfig']['queueId'] ?? 0);
        $queue = $this->queues->details($queueId);
        $members = $this->normalizeMembers($raw['members'] ?? [], $raw['localMember'] ?? []);

        $searching = $gameflow === 'Matchmaking';
        $readyCheck = $gameflow === 'ReadyCheck' ? $this->readyCheck() : null;

        return [
            'queueId' => $queueId,
            'queue' => $queue,
            'isCustom' => (bool) ($raw['gameConfig']['isCustom'] ?? false),
            'chatId' => $raw['chat']['id'] ?? null,
            'members' => $members,
            'slots' => $this->slots($queue, $members),
            'ownerId' => $this->ownerId($members),
            'local' => $this->localMember($raw['localMember'] ?? []),
            'playerCount' => count($members),
            'maxPlayers' => $queue['players'],
            'canStart' => (bool) ($raw['canStartActivity'] ?? false),
            'searching' => $searching,
            'readyCheck' => $readyCheck,
            'gameflow' => $gameflow,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeMembers(?array $raw, array $local): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $localId = isset($local['summonerId']) ? (string) $local['summonerId'] : null;

        $members = [];

        foreach ($raw as $member) {
            if (! is_array($member)) {
                continue;
            }

            $summonerId = (string) ($member['summonerId'] ?? '');

            $members[] = [
                'summonerId' => $summonerId,
                'puuid' => $member['puuid'] ?? null,
                'gameName' => $member['gameName'] ?? $member['summonerName'] ?? 'Summoner',
                'tagLine' => $member['gameTag'] ?? null,
                'icon' => isset($member['profileIconId']) ? (int) $member['profileIconId'] : null,
                'level' => isset($member['summonerLevel']) ? (int) $member['summonerLevel'] : null,
                'isOwner' => (bool) ($member['isLeader'] ?? false),
                'position' => $member['position'] ?? null,
                'ready' => (bool) ($member['ready'] ?? false),
                'isLocal' => $localId !== null && $summonerId !== '' && $summonerId === $localId,
            ];
        }

        return $members;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function localMember(array $raw): array
    {
        return [
            'summonerId' => (string) ($raw['summonerId'] ?? ''),
            'position' => $raw['position'] ?? null,
            'isOwner' => (bool) ($raw['isLeader'] ?? false),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     */
    private function ownerId(array $members): ?string
    {
        foreach ($members as $member) {
            if ($member['isOwner']) {
                return $member['summonerId'];
            }
        }

        return null;
    }

    /**
     * A lobby is startable when the client reports the activity is ready.
     *
     * @param  array{players: int}  $queue
     * @param  array<int, array<string, mixed>>  $members
     */
    private function canStart(array $queue, int $playerCount, string $gameflow): bool
    {
        if (in_array($gameflow, ['Matchmaking', 'ReadyCheck', 'ChampSelect'], true)) {
            return false;
        }

        $required = $queue['players'];

        return $playerCount >= $required;
    }

    /**
     * Lay members out into the fixed slot grid for the queue: positional
     * queues place members on their preferred role first, everyone else fills
     * the remaining slots, and empty slots are padded so the UI is stable.
     *
     * @param  array{players: int, positions: bool}  $queue
     * @param  array<int, array<string, mixed>>  $members
     * @return array<int, array{position: string, member: array<string, mixed>|null}>
     */
    private function slots(array $queue, array $members): array
    {
        $count = $queue['players'];
        $slots = [];
        $remaining = [];

        if ($queue['positions']) {
            $byPosition = [];
            $placedIds = [];

            foreach ($members as $member) {
                $byPosition[strtoupper((string) ($member['position'] ?? ''))][] = $member;
            }

            foreach ($this->queues->positions() as $position) {
                $bucket = $byPosition[$position] ?? [];
                $member = array_shift($bucket) ?? null;
                $slots[] = ['position' => $position, 'member' => $member];

                if ($member !== null) {
                    $placedIds[$member['summonerId']] = true;
                }
            }

            foreach ($members as $member) {
                if (! isset($placedIds[$member['summonerId']])) {
                    $remaining[] = $member;
                }
            }

            foreach ($slots as $index => $slot) {
                if ($slot['member'] === null && $remaining) {
                    $slots[$index]['member'] = array_shift($remaining);
                }
            }
        } else {
            $remaining = $members;
        }

        foreach ($remaining as $member) {
            $slots[] = ['position' => $member['position'] ?: 'FILL', 'member' => $member];
        }

        while (count($slots) < $count) {
            $slots[] = ['position' => 'FILL', 'member' => null];
        }

        return array_slice($slots, 0, $count);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readyCheck(): ?array
    {
        $raw = $this->bestEffort('/lol-matchmaking/v1/ready-check');

        if (! is_array($raw)) {
            return null;
        }

        $members = [];

        foreach ($raw['members'] ?? [] as $member) {
            if (is_array($member)) {
                $members[] = [
                    'summonerId' => (string) ($member['summonerId'] ?? ''),
                    'gameName' => $member['gameName'] ?? 'Summoner',
                    'isReady' => (bool) ($member['isReady'] ?? false),
                ];
            }
        }

        return [
            'state' => (string) ($raw['state'] ?? 'InProgress'),
            'members' => $members,
            'timeLimitMs' => (int) ($raw['timeLimitInMs'] ?? 30000),
            'declinerIds' => array_map('strval', (array) ($raw['declinerIds'] ?? [])),
        ];
    }

    /**
     * Run a lobby action and wrap the outcome into a uniform result.
     *
     * @param  array<string, mixed>  $body
     * @return array{ok: bool, error?: string, body?: mixed}
     */
    private function attempt(string $method, string $path, array $body = []): array
    {
        try {
            $response = $this->client->send($method, $path, $body);
        } catch (ClientNotRunningException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (Throwable) {
            return ['ok' => false, 'error' => 'The League client is not responding.'];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'error' => $this->errorMessage($response)];
        }

        return ['ok' => true, 'body' => $response->json()];
    }

    private function errorMessage(Response $response): string
    {
        $payload = $response->json();

        if (is_array($payload)) {
            return (string) ($payload['errorMessage'] ?? $payload['message'] ?? $payload['errorCode'] ?? 'The League client rejected the request.');
        }

        $body = trim($response->body());

        return $body !== '' ? $body : 'The League client rejected the request.';
    }

    private function bestEffort(string $path): mixed
    {
        try {
            $response = $this->client->send('GET', $path);

            return $response->successful() ? $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }
}
