<?php

namespace App\Services\LeagueClient;

use Throwable;

class FriendProvider
{
    private const FRIEND_STATUS = [
        'chat' => ['Online', 'bg-vine', 'text-vine', 'border-vine/30 bg-vine/10'],
        'in-game' => ['In game', 'bg-ember', 'text-ember', 'border-ember/30 bg-ember/10'],
        'mobile' => ['Mobile', 'bg-cerulean', 'text-cerulean', 'border-cerulean/30 bg-cerulean/10'],
        'away' => ['Away', 'bg-gold', 'text-gold', 'border-gold/30 bg-gold/10'],
        'dnd' => ['Do not disturb', 'bg-ember', 'text-ember', 'border-ember/30 bg-ember/10'],
        'spectating' => ['Spectating', 'bg-arcane', 'text-arcane', 'border-arcane/30 bg-arcane/10'],
        'offline' => ['Offline', 'bg-mist', 'text-mist', 'border-mist/30 bg-mist/10'],
    ];

    private const FRIEND_STATUS_ORDER = [
        'chat' => 0,
        'in-game' => 1,
        'mobile' => 2,
        'spectating' => 3,
        'dnd' => 4,
        'away' => 5,
        'offline' => 6,
    ];

    public function __construct(protected LeagueClientConnector $client) {}

    /**
     * Fetch and normalize the friend list, degrading to an empty array when
     * the client is offline.
     *
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        try {
            return $this->normalize($this->client->request('GET', '/lol-chat/v1/friends'));
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Normalize the raw LCU friend entries into the display shape used by the
     * dashboard card and the lobby invite panel.
     *
     * @return array<int, array<string, mixed>>
     */
    public function normalize(?array $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $friends = [];

        foreach ($raw as $friend) {
            if (! is_array($friend)) {
                continue;
            }

            $availability = $friend['availability'] ?? 'offline';
            [$status, $dot, $text, $pill] = self::FRIEND_STATUS[$availability] ?? ['Offline', 'bg-mist', 'text-mist', 'border-mist/30 bg-mist/10'];

            $friends[] = [
                'key' => $availability,
                'name' => $friend['gameName'] ?? $friend['name'] ?? 'Summoner',
                'summonerId' => (string) ($friend['summonerId'] ?? $friend['id'] ?? ''),
                'status' => $status,
                'availability' => $availability,
                'invitable' => in_array($availability, ['chat', 'mobile', 'away', 'dnd'], true),
                'dot' => $dot,
                'text' => $text,
                'pill' => $pill,
                'icon' => isset($friend['icon']) ? (int) $friend['icon'] : null,
            ];
        }

        usort($friends, function (array $a, array $b): int {
            $aRank = self::FRIEND_STATUS_ORDER[$a['key']] ?? 9;
            $bRank = self::FRIEND_STATUS_ORDER[$b['key']] ?? 9;

            return $aRank <=> $bRank;
        });

        return $friends;
    }
}
