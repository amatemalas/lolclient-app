<?php

namespace App\Services\LeagueClient;

use Carbon\Carbon;
use Throwable;

class DashboardProvider
{
    public function __construct(
        protected LeagueClientConnector $client,
        protected FriendProvider $friends,
        protected QueueProvider $queues,
    ) {}

    /**
     * Assemble the dashboard data set. Every section degrades gracefully to a
     * safe default when the client is offline or a specific endpoint is down.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $data = $this->shell();

        $data['ranked'] = $this->defaultRanked();
        $data['matches'] = [];
        $data['friends'] = [];
        $data['missions'] = [];

        if ($data['connected']) {
            $data['ranked'] = $this->normalizeRanked($this->bestEffort('/lol-ranked/v1/current-ranked-stats'));
            $champions = $this->championMap();
            $data['matches'] = $this->recentMatches($data['summoner'], $champions, $this->itemMap());
            $data['friends'] = $this->friends->list();
            $data['missions'] = $this->normalizeMissions($this->bestEffort('/lol-missions/v1/missions'));
        }

        return $data;
    }

    /**
     * Lightweight data set shared by every page shell (sidebar/topbar):
     * connection state, current phase, summoner and wallet.
     *
     * @return array<string, mixed>
     */
    public function shell(): array
    {
        $connected = false;
        $error = null;
        $gameflow = 'None';

        $summoner = $this->defaultSummoner();
        $wallet = ['rp' => 0, 'be' => 0];

        try {
            $gameflow = $this->stringOr($this->client->request('GET', '/lol-gameflow/v1/gameflow-phase'), 'None');
            $connected = true;
        } catch (ClientNotRunningException $e) {
            $error = $e->getMessage();
        } catch (Throwable) {
            $error = 'The League client is not responding.';
        }

        if ($connected) {
            $summoner = $this->normalizeSummoner($this->bestEffort('/lol-summoner/v1/current-summoner'));
            $wallet = $this->normalizeWallet($this->bestEffort('/lol-inventory/v1/wallet', ['currencyTypes' => 'IP,RP']));
        }

        return compact('connected', 'error', 'gameflow', 'summoner', 'wallet');
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultSummoner(): array
    {
        return [
            'gameName' => 'Summoner',
            'tagLine' => '',
            'summonerLevel' => 1,
            'profileIconId' => null,
            'puuid' => null,
            'summonerId' => null,
            'percentCompleteForNextLevel' => 0,
            'xpSinceLastLevel' => 0,
            'xpUntilNextLevel' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeSummoner(?array $raw): array
    {
        $raw = $raw ?? [];

        return [
            'gameName' => $raw['gameName'] ?? 'Summoner',
            'tagLine' => $raw['tagLine'] ?? $raw['displayName'] ?? '',
            'summonerLevel' => (int) ($raw['summonerLevel'] ?? 1),
            'profileIconId' => isset($raw['profileIconId']) ? (int) $raw['profileIconId'] : null,
            'puuid' => $raw['puuid'] ?? null,
            'summonerId' => isset($raw['summonerId']) ? (string) $raw['summonerId'] : null,
            'percentCompleteForNextLevel' => (int) ($raw['percentCompleteForNextLevel'] ?? 0),
            'xpSinceLastLevel' => (int) ($raw['xpSinceLastLevel'] ?? 0),
            'xpUntilNextLevel' => (int) ($raw['xpUntilNextLevel'] ?? 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultRanked(): array
    {
        return [
            'tier' => 'UNRANKED',
            'division' => '',
            'leaguePoints' => 0,
            'wins' => 0,
            'losses' => 0,
            'games' => 0,
            'winRate' => 0.0,
            'isProvisional' => false,
            'provisionalGamesRemaining' => 0,
            'ranked' => false,
            'promos' => '',
            'lpToNext' => 0,
            'nextDivision' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeRanked(?array $raw): array
    {
        $entry = $raw['queueMap']['RANKED_SOLO_5x5'] ?? null;

        if (! is_array($entry)) {
            return $this->defaultRanked();
        }

        $wins = (int) ($entry['wins'] ?? 0);
        $losses = (int) ($entry['losses'] ?? 0);
        $games = $wins + $losses;

        return [
            'tier' => $entry['tier'] ?? 'UNRANKED',
            'division' => $entry['division'] ?? '',
            'leaguePoints' => (int) ($entry['leaguePoints'] ?? 0),
            'wins' => $wins,
            'losses' => $losses,
            'games' => $games,
            'winRate' => $games > 0 ? round($wins / $games * 100, 1) : 0.0,
            'isProvisional' => (bool) ($entry['isProvisional'] ?? false),
            'provisionalGamesRemaining' => (int) ($entry['provisionalGamesRemaining'] ?? 0),
            'ranked' => (bool) ($entry['ranked'] ?? false),
            'promos' => (string) ($entry['miniSeriesProgress'] ?? ''),
            'lpToNext' => max(0, 100 - (int) ($entry['leaguePoints'] ?? 0)),
            'nextDivision' => $this->nextDivision($entry['division'] ?? null),
        ];
    }

    /**
     * @return array{rp: int, be: int}
     */
    private function normalizeWallet(?array $raw): array
    {
        $raw = $raw ?? [];

        return [
            'rp' => (int) ($raw['rp'] ?? 0),
            'be' => (int) ($raw['ip'] ?? $raw['be'] ?? 0),
        ];
    }

    /**
     * Map numeric champion ids to display names and portrait paths.
     *
     * @return array<int, array{name: string}>
     */
    private function championMap(): array
    {
        $list = $this->bestEffort('/lol-game-data/assets/v1/champion-summary.json');

        if (! is_array($list)) {
            return [];
        }

        $map = [];

        foreach ($list as $champion) {
            if (is_array($champion) && isset($champion['id'], $champion['name'])) {
                $map[(int) $champion['id']] = ['name' => (string) $champion['name']];
            }
        }

        return $map;
    }

    /**
     * Map numeric item ids to their proxied icon asset paths. Item icons are
     * NOT available at a predictable v1/items/{id}.png URL — each item carries
     * an iconPath in items.json, so we resolve the path once per request.
     *
     * @return array<int, string>
     */
    private function itemMap(): array
    {
        $list = $this->bestEffort('/lol-game-data/assets/v1/items.json');

        if (! is_array($list)) {
            return [];
        }

        $map = [];

        foreach ($list as $item) {
            if (! is_array($item) || ! isset($item['id'], $item['iconPath'])) {
                continue;
            }

            $icon = str_replace('/lol-game-data/assets/', '', (string) $item['iconPath']);

            $map[(int) $item['id']] = strtolower(ltrim($icon, '/'));
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $summoner
     * @param  array<int, array{name: string}>  $champions
     * @param  array<int, string>  $itemIcons
     * @return array<int, array<string, mixed>>
     */
    private function recentMatches(array $summoner, array $champions, array $itemIcons): array
    {
        $puuid = $summoner['puuid'] ?? null;
        $summonerId = $summoner['summonerId'] ?? null;
        $gameName = $summoner['gameName'] ?? null;

        if (! $puuid) {
            return [];
        }

        $list = $this->bestEffort('/lol-match-history/v1/products/lol/'.$puuid.'/matches');
        $games = $list['games']['games'] ?? null;

        if (! is_array($games)) {
            return [];
        }

        $matches = [];

        foreach (array_slice($games, 0, 5) as $game) {
            $local = $this->localParticipant($game, $puuid, $summonerId, $gameName);

            if ($local === null || ! isset($local['stats']) || ! is_array($local['stats'])) {
                continue;
            }

            $stats = $local['stats'];
            $championId = (int) ($local['championId'] ?? 0);
            $duration = (int) ($game['gameDuration'] ?? 0);
            $cs = (int) ($stats['totalMinionsKilled'] ?? 0) + (int) ($stats['neutralMinionsKilled'] ?? 0);

            $items = [];
            foreach ([0, 1, 2, 3, 4, 6] as $slot) {
                $itemId = (int) ($stats['item'.$slot] ?? 0);
                $items[] = $itemId > 0 ? ($itemIcons[$itemId] ?? null) : null;
            }

            $matches[] = [
                'win' => (bool) ($stats['win'] ?? false),
                'championId' => $championId,
                'champion' => $champions[$championId]['name'] ?? null,
                'mode' => $this->queues->supports((int) ($game['queueId'] ?? 0))
                    ? $this->queues->details((int) ($game['queueId'] ?? 0))['name']
                    : (string) ($game['gameMode'] ?? 'Match'),
                'duration' => $this->formatDuration($duration),
                'durationSeconds' => $duration,
                'ago' => $this->relativeTime($game),
                'kills' => (int) ($stats['kills'] ?? 0),
                'deaths' => (int) ($stats['deaths'] ?? 0),
                'assists' => (int) ($stats['assists'] ?? 0),
                'cs' => $cs,
                'gold' => $this->formatGold((int) ($stats['goldEarned'] ?? 0)),
                'champLevel' => (int) ($stats['champLevel'] ?? 0),
                'items' => $items,
            ];
        }

        return $matches;
    }

    /**
     * Locate the current player's participant entry in a match history game.
     *
     * @param  array<string, mixed>  $game
     * @return array<string, mixed>|null
     */
    private function localParticipant(array $game, ?string $puuid, ?string $summonerId, ?string $gameName): ?array
    {
        $identities = [];

        foreach ($game['participantIdentities'] ?? [] as $identity) {
            if (is_array($identity) && isset($identity['participantId'])) {
                $identities[$identity['participantId']] = $identity;
            }
        }

        foreach ($game['participants'] ?? [] as $participant) {
            if (! is_array($participant)) {
                continue;
            }

            $player = $identities[$participant['participantId'] ?? null]['player'] ?? null;

            if (! is_array($player)) {
                continue;
            }

            if ($puuid && isset($player['puuid']) && $player['puuid'] === $puuid) {
                return $participant;
            }

            if ($summonerId && isset($player['summonerId']) && (string) $player['summonerId'] === $summonerId) {
                return $participant;
            }

            if ($gameName && isset($player['gameName']) && strcasecmp($player['gameName'], $gameName) === 0) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeMissions(?array $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $missions = [];

        foreach ($raw as $mission) {
            if (! is_array($mission)) {
                continue;
            }

            if (isset($mission['completedDate']) || in_array($mission['status'] ?? null, ['REWARD_CLAIMED', 'FINISHED', 'COMPLETED'], true)) {
                continue;
            }

            $objective = $mission['objectives'][0] ?? null;
            $progress = is_array($objective) ? ($objective['progress'] ?? null) : null;

            if (! is_array($progress)) {
                continue;
            }

            $done = (int) ($progress['currentProgress'] ?? 0);
            $total = (int) ($progress['totalCount'] ?? 0);

            if ($total <= 0) {
                continue;
            }

            $missions[] = [
                'title' => $mission['title'] ?? $mission['internalName'] ?? 'Mission',
                'done' => $done,
                'total' => $total,
            ];
        }

        return array_slice($missions, 0, 2);
    }

    /**
     * Fetch an endpoint, returning null instead of throwing when the LCU
     * responds badly to a non-critical request.
     *
     * @param  array<string, mixed>  $query
     */
    private function bestEffort(string $path, array $query = []): mixed
    {
        try {
            $response = $this->client->send('GET', $path, [], $query);

            return $response->successful() ? $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function stringOr(mixed $value, string $default): string
    {
        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function nextDivision(?string $division): string
    {
        $order = ['IV' => 'III', 'III' => 'II', 'II' => 'I', 'I' => ''];

        return $order[$division ?? ''] ?? '';
    }

    private function formatDuration(int $seconds): string
    {
        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    private function formatGold(int $gold): string
    {
        return number_format($gold / 1000, 1).'k';
    }

    /**
     * @param  array<string, mixed>  $game
     */
    private function relativeTime(array $game): string
    {
        if (! empty($game['gameCreationDate']) && is_string($game['gameCreationDate'])) {
            return Carbon::parse($game['gameCreationDate'])->diffForHumans();
        }

        if (! empty($game['gameCreation'])) {
            return Carbon::createFromTimestampMs((int) $game['gameCreation'])->diffForHumans();
        }

        return 'recently';
    }
}
