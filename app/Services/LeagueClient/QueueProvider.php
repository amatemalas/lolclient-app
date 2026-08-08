<?php

namespace App\Services\LeagueClient;

use Throwable;

class QueueProvider
{
    public function __construct(protected LeagueClientConnector $client) {}

    /**
     * Static queue details used as a fallback when the local client cannot be
     * reached (or does not know about a queue). Every entry is overridable by
     * the live /lol-game-queues/v1/queues/{id} payload.
     *
     * @var array<int, array{name: string, description: string, map: string, icon: string, players: int, positions: bool}>
     */
    private const QUEUE_DETAILS = [
        400 => ['name' => 'Normal Draft', 'description' => 'Draft pick on Summoner\'s Rift.', 'map' => 'Summoner\'s Rift', 'icon' => 'aram', 'players' => 5, 'positions' => true],
        420 => ['name' => 'Ranked Solo', 'description' => 'Solo/duo ranked queue.', 'map' => 'Summoner\'s Rift', 'icon' => 'ranked', 'players' => 5, 'positions' => true],
        430 => ['name' => 'Normal Blind', 'description' => 'Blind pick on Summoner\'s Rift.', 'map' => 'Summoner\'s Rift', 'icon' => 'aram', 'players' => 5, 'positions' => true],
        440 => ['name' => 'Flex', 'description' => 'Ranked flex queue.', 'map' => 'Summoner\'s Rift', 'icon' => 'aram', 'players' => 5, 'positions' => true],
        450 => ['name' => 'ARAM', 'description' => 'All Random All Mid on the Howling Abyss.', 'map' => 'Howling Abyss', 'icon' => 'aram', 'players' => 5, 'positions' => false],
        700 => ['name' => 'Clash', 'description' => 'Organized 5v5 tournament.', 'map' => 'Summoner\'s Rift', 'icon' => 'clash', 'players' => 5, 'positions' => true],
        720 => ['name' => 'Clash', 'description' => 'Organized 5v5 tournament.', 'map' => 'Summoner\'s Rift', 'icon' => 'clash', 'players' => 5, 'positions' => true],
        1700 => ['name' => 'Arena', 'description' => '2v2v2v2 brawl on the Ring.', 'map' => 'The Ring', 'icon' => 'aram', 'players' => 8, 'positions' => false],
    ];

    /**
     * Modes offered from the dashboard "Play" strip, in display order.
     *
     * @return array<int, array{id: int, name: string, queue: string, featured: bool, icon: string, disabled: bool}>
     */
    public function dashboardModes(): array
    {
        return [
            ['id' => 420, 'name' => 'Ranked Solo', 'queue' => '5v5 · Ranked', 'featured' => true, 'icon' => 'ranked', 'disabled' => false],
            ['id' => 400, 'name' => 'Normal', 'queue' => '5v5 · Draft', 'featured' => false, 'icon' => 'aram', 'disabled' => false],
            ['id' => 440, 'name' => 'Flex', 'queue' => '5v5 · Flex', 'featured' => false, 'icon' => 'aram', 'disabled' => false],
            ['id' => 450, 'name' => 'ARAM', 'queue' => 'Howling Abyss', 'featured' => false, 'icon' => 'aram', 'disabled' => false],
        ];
    }

    /**
     * Resolve a single queue, preferring live data from the client and falling
     * back to the static catalog.
     *
     * @return array{id: int, name: string, description: string, map: string, icon: string, players: int, positions: bool}
     */
    public function details(int $queueId): array
    {
        $fallback = self::QUEUE_DETAILS[$queueId] ?? self::QUEUE_DETAILS[400];
        $raw = $this->bestEffort('/lol-game-queues/v1/queues/'.$queueId);

        if (! is_array($raw)) {
            $fallback['id'] = $queueId;

            return $fallback;
        }

        return [
            'id' => (int) ($raw['id'] ?? $queueId),
            'name' => (string) ($raw['name'] ?? $fallback['name']),
            'description' => (string) ($raw['description'] ?? $fallback['description']),
            'map' => (string) ($fallback['map']),
            'icon' => (string) ($fallback['icon']),
            'players' => (int) ($fallback['players']),
            'positions' => (bool) ($fallback['positions']),
        ];
    }

    /**
     * Whether the app can create a lobby for the given queue id.
     */
    public function supports(int $queueId): bool
    {
        return array_key_exists($queueId, self::QUEUE_DETAILS);
    }

    /**
     * The ordered roles rendered for queues that use position pick.
     *
     * @return array<int, string>
     */
    public function positions(): array
    {
        return ['TOP', 'JUNGLE', 'MIDDLE', 'BOTTOM', 'UTILITY'];
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
