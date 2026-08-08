<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    private string $lockfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lockfile = tempnam(sys_get_temp_dir(), 'lockfile_');
        file_put_contents($this->lockfile, 'LeagueClient:1234:51705:secret-token:https');

        config(['leagueclient.lockfile_path' => $this->lockfile]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->lockfile)) {
            unlink($this->lockfile);
        }

        parent::tearDown();
    }

    public function test_status_reports_the_client_is_connected(): void
    {
        Http::fake([
            'https://127.0.0.1:51705/*' => Http::response('"Lobby"', 200, ['Content-Type' => 'application/json']),
        ]);

        $this->getJson('/api/lcu/status')
            ->assertOk()
            ->assertJson([
                'connected' => true,
                'gameflow' => 'Lobby',
            ]);
    }

    public function test_status_reports_the_client_is_not_connected(): void
    {
        config(['leagueclient.lockfile_path' => '/nonexistent/lockfile']);

        $this->getJson('/api/lcu/status')
            ->assertOk()
            ->assertJson(['connected' => false]);
    }

    public function test_dashboard_renders_live_client_data(): void
    {
        $this->fakeClientEndpoints();

        $this->get('/')
            ->assertOk()
            ->assertSee('Connected')
            ->assertSee('Test Summoner')
            ->assertSee('DIAMOND')
            ->assertSee('Miss Fortune')
            ->assertSee('12')
            ->assertSee('23,480')
            ->assertSee('Win 2 games')
            ->assertSee('KatarinaMain')
            ->assertSee('assets/items/icons2d/1001_class_t1_bootsofspeed.png')
            ->assertSee('assets/items/icons2d/3340_class_t1_wardingtotem.png');
    }

    public function test_dashboard_redirects_to_launcher_required_when_client_is_not_running(): void
    {
        config(['leagueclient.lockfile_path' => '/nonexistent/lockfile']);

        $this->get('/')
            ->assertRedirect(route('launcher.required'));

        $this->get(route('launcher.required'))
            ->assertOk()
            ->assertSee('Log into the League of Legends launcher to use this app.');
    }

    public function test_dashboard_degrades_gracefully_when_an_endpoint_fails(): void
    {
        $this->fakeClientEndpoints([
            'https://127.0.0.1:51705/lol-chat/*' => Http::response(null, 500),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Test Summoner')
            ->assertSee('No friends online');
    }

    public function test_asset_proxies_client_images(): void
    {
        Http::fake([
            'https://127.0.0.1:51705/*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/png']),
        ]);

        $this->get('/api/lcu/assets/v1/profile-icons/4567.jpg')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'immutable, max-age=604800, public')
            ->assertHeader('Content-Length', '16')
            ->assertSee('fake-image-bytes');

        Http::assertSent(
            fn ($request) => $request->url() === 'https://127.0.0.1:51705/lol-game-data/assets/v1/profile-icons/4567.jpg'
        );
    }

    public function test_asset_returns_404_when_client_is_not_running(): void
    {
        config(['leagueclient.lockfile_path' => '/nonexistent/lockfile']);

        $this->get('/api/lcu/assets/v1/profile-icons/4567.jpg')
            ->assertNotFound();
    }

    private function fakeClientEndpoints(array $overrides = []): void
    {
        Http::fake(array_replace([
            'https://127.0.0.1:51705/lol-gameflow/*' => Http::response('"Lobby"', 200),
            'https://127.0.0.1:51705/lol-summoner/*' => Http::response([
                'gameName' => 'Test Summoner',
                'tagLine' => 'EUW',
                'summonerLevel' => 128,
                'profileIconId' => 4567,
                'puuid' => 'puuid-123',
                'summonerId' => 987654,
                'percentCompleteForNextLevel' => 74,
                'xpSinceLastLevel' => 2340,
                'xpUntilNextLevel' => 3150,
            ], 200),
            'https://127.0.0.1:51705/lol-ranked/*' => Http::response([
                'queueMap' => [
                    'RANKED_SOLO_5x5' => [
                        'tier' => 'DIAMOND',
                        'division' => 'III',
                        'leaguePoints' => 47,
                        'wins' => 45,
                        'losses' => 38,
                        'ranked' => true,
                        'isProvisional' => false,
                        'provisionalGamesRemaining' => 0,
                        'miniSeriesProgress' => '',
                    ],
                ],
            ], 200),
            'https://127.0.0.1:51705/lol-inventory/*' => Http::response([
                'rp' => 1540,
                'ip' => 23480,
            ], 200),
            'https://127.0.0.1:51705/lol-match-history/*' => Http::response([
                'games' => [
                    'gameCount' => 1,
                    'games' => [[
                        'gameId' => 1,
                        'queueId' => 420,
                        'gameMode' => 'CLASSIC',
                        'gameDuration' => 1694,
                        'gameCreation' => now()->subHours(2)->valueOf(),
                        'gameCreationDate' => now()->subHours(2)->toIso8601String(),
                        'participantIdentities' => [
                            ['participantId' => 1, 'player' => ['summonerId' => 987654, 'puuid' => 'puuid-123', 'gameName' => 'Test Summoner']],
                            ['participantId' => 2, 'player' => ['summonerId' => 111111, 'puuid' => 'other-puuid', 'gameName' => 'Enemy']],
                        ],
                        'participants' => [
                            ['participantId' => 1, 'championId' => 21, 'teamId' => 100, 'stats' => [
                                'win' => true, 'kills' => 12, 'deaths' => 4, 'assists' => 7,
                                'totalMinionsKilled' => 231, 'neutralMinionsKilled' => 0,
                                'goldEarned' => 13200, 'champLevel' => 15,
                                'item0' => 1001, 'item1' => 3111, 'item2' => 3128, 'item3' => 3047, 'item4' => 3135, 'item6' => 3340,
                            ]],
                            ['participantId' => 2, 'championId' => 22, 'teamId' => 200, 'stats' => [
                                'win' => false, 'kills' => 3, 'deaths' => 9, 'assists' => 6,
                                'totalMinionsKilled' => 189, 'neutralMinionsKilled' => 0,
                                'goldEarned' => 9800, 'champLevel' => 13,
                            ]],
                        ],
                    ]],
                ],
            ], 200),
            'https://127.0.0.1:51705/lol-game-data/assets/v1/items.json' => Http::response([
                ['id' => 1001, 'iconPath' => '/lol-game-data/assets/ASSETS/Items/Icons2D/1001_class_t1_bootsofspeed.png'],
                ['id' => 3111, 'iconPath' => '/lol-game-data/assets/ASSETS/Items/Icons2D/3111_class_t2_mercurystreads.png'],
                ['id' => 3128, 'iconPath' => '/lol-game-data/assets/ASSETS/Items/Icons2D/3128_deathfire_grasp.png'],
                ['id' => 3047, 'iconPath' => '/lol-game-data/assets/ASSETS/Items/Icons2D/3047_class_t2_ninjatabi.png'],
                ['id' => 3135, 'iconPath' => '/lol-game-data/assets/ASSETS/Items/Icons2D/3135_mage_t3_voidstaff.png'],
                ['id' => 3340, 'iconPath' => '/lol-game-data/assets/ASSETS/Items/Icons2D/3340_class_t1_wardingtotem.png'],
            ], 200),
            'https://127.0.0.1:51705/lol-game-data/*' => Http::response([
                ['id' => 21, 'name' => 'Miss Fortune'],
                ['id' => 22, 'name' => 'Ashe'],
            ], 200),
            'https://127.0.0.1:51705/lol-chat/*' => Http::response([
                ['name' => 'KatarinaMain', 'availability' => 'chat', 'icon' => 1234],
                ['name' => 'Darius Bot', 'availability' => 'in-game', 'icon' => 2345],
                ['name' => 'Support Diff', 'availability' => 'away', 'icon' => 3456],
            ], 200),
            'https://127.0.0.1:51705/lol-missions/*' => Http::response([
                ['title' => 'Win 2 games', 'status' => 'STARTED', 'objectives' => [
                    ['progress' => ['currentProgress' => 1, 'totalCount' => 2]],
                ]],
            ], 200),
        ], $overrides));
    }
}
