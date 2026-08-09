<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LobbyTest extends TestCase
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

    public function test_lobby_page_seeds_the_vue_panel_with_the_current_lobby(): void
    {
        $this->fakeClient();

        $this->get('/lobby')
            ->assertOk()
            ->assertSee('id="lobby-app"', false)
            ->assertSee('lobby-initial')
            ->assertSee('Ranked Solo')
            ->assertSee('KatarinaMain')
            ->assertSee('Darius Bot')
            ->assertSee('Test Summoner')
            ->assertSee('TOP')
            ->assertSee('JUNGLE');
    }

    public function test_lobby_page_redirects_to_launcher_required_when_client_is_offline(): void
    {
        config(['leagueclient.lockfile_path' => '/nonexistent/lockfile']);

        $this->get('/lobby')
            ->assertRedirect(route('launcher.required'));
    }

    public function test_lobby_show_returns_the_current_lobby_state(): void
    {
        $this->fakeClient();

        $this->getJson('/api/lcu/lobby')
            ->assertOk()
            ->assertJson([
                'connected' => true,
                'gameflow' => 'Lobby',
            ])
            ->assertJsonPath('lobby.queueId', 420)
            ->assertJsonPath('lobby.playerCount', 3)
            ->assertJsonPath('lobby.local.isOwner', true);
    }

    public function test_lobby_show_collapses_to_a_light_payload_when_state_is_unchanged(): void
    {
        $this->fakeClient();

        $first = $this->getJson('/api/lcu/lobby')->assertOk()->json();

        $this->assertTrue($first['changed']);
        $this->assertArrayHasKey('signature', $first);
        $this->assertNotNull($first['lobby']);

        $second = $this->getJson('/api/lcu/lobby?rev='.$first['signature'])->assertOk()->json();

        $this->assertFalse($second['changed']);
        $this->assertArrayHasKey('signature', $second);
        $this->assertNull($second['lobby']);

        Http::assertSentCount(5);
    }

    public function test_lobby_store_creates_a_lobby_for_the_requested_queue(): void
    {
        Http::fake([
            'https://127.0.0.1:51705/lol-gameflow/*' => Http::response('"Lobby"', 200),
            'https://127.0.0.1:51705/lol-lobby/v2/lobby' => Http::sequence()
                ->push(['errorCode' => 'RPC_ERROR', 'httpStatus' => 404, 'message' => 'LOBBY_NOT_FOUND'], 404)
                ->push($this->lobbyPayload(450)),
            'https://127.0.0.1:51705/*' => Http::response(null, 404),
        ]);

        $this->postJson('/api/lcu/lobby', ['queue_id' => 450])
            ->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonPath('lobby.queueId', 450);

        Http::assertSent(
            fn ($request) => $request->method() === 'POST'
                && $request->url() === 'https://127.0.0.1:51705/lol-lobby/v2/lobby'
                && $request['queueId'] === 450
        );
        Http::assertNotSent(
            fn ($request) => $request->method() === 'DELETE'
        );
    }

    public function test_lobby_store_leaves_the_current_lobby_before_switching_modes(): void
    {
        Http::fake([
            'https://127.0.0.1:51705/lol-gameflow/*' => Http::response('"Lobby"', 200),
            'https://127.0.0.1:51705/lol-lobby/v2/lobby' => Http::sequence()
                ->push($this->lobbyPayload())
                ->push(null, 200)
                ->push($this->lobbyPayload(440)),
            'https://127.0.0.1:51705/*' => Http::response(null, 404),
        ]);

        $this->postJson('/api/lcu/lobby', ['queue_id' => 440])
            ->assertOk()
            ->assertJsonPath('lobby.queueId', 440);

        Http::assertSent(
            fn ($request) => $request->method() === 'DELETE'
                && $request->url() === 'https://127.0.0.1:51705/lol-lobby/v2/lobby'
        );
        Http::assertSent(
            fn ($request) => $request->method() === 'POST'
                && $request->url() === 'https://127.0.0.1:51705/lol-lobby/v2/lobby'
                && $request['queueId'] === 440
        );
    }

    public function test_lobby_store_rejects_an_unknown_queue(): void
    {
        $this->fakeClient();

        $this->postJson('/api/lcu/lobby', ['queue_id' => 999])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_lobby_destroy_leaves_the_lobby(): void
    {
        Http::fake([
            'https://127.0.0.1:51705/*' => Http::response(null, 200),
        ]);

        $this->deleteJson('/api/lcu/lobby')
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::assertSent(
            fn ($request) => $request->method() === 'DELETE'
                && $request->url() === 'https://127.0.0.1:51705/lol-lobby/v2/lobby'
        );
    }

    public function test_lobby_matchmaking_search_can_start_and_stop(): void
    {
        Http::fake([
            'https://127.0.0.1:51705/lol-lobby/v2/lobby/matchmaking/search' => Http::response(null, 200),
        ]);

        $this->postJson('/api/lcu/lobby/matchmaking/search')
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->deleteJson('/api/lcu/lobby/matchmaking/search')
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::assertSentCount(2);
    }

    public function test_lobby_invite_sends_an_invitation_to_a_friend(): void
    {
        $this->fakeClient();

        $this->postJson('/api/lcu/lobby/members/111111/invite')
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::assertSent(
            fn ($request) => $request->method() === 'POST'
                && $request->url() === 'https://127.0.0.1:51705/lol-lobby/v2/lobby/invitations'
                && $request[0]['toSummonerId'] === 111111
        );
    }

    public function test_lobby_enriches_members_from_the_summoner_endpoint(): void
    {
        $payload = $this->lobbyPayload();
        $payload['members'] = [
            ['summonerId' => 987654, 'puuid' => 'puuid-123', 'summonerName' => '', 'summonerIconId' => 7191, 'summonerLevel' => 181, 'isLeader' => true, 'position' => 'FILL', 'ready' => true],
        ];

        $this->fakeClient([
            'https://127.0.0.1:51705/lol-lobby/v2/lobby' => Http::response($payload, 200),
        ]);

        $this->getJson('/api/lcu/lobby')
            ->assertOk()
            ->assertJsonPath('lobby.members.0.gameName', 'Test Summoner')
            ->assertJsonPath('lobby.members.0.tagLine', 'EUW')
            ->assertJsonPath('lobby.members.0.icon', 7191);

        Http::assertSent(
            fn ($request) => $request->url() === 'https://127.0.0.1:51705/lol-summoner/v1/summoners/987654'
        );
    }

    public function test_lobby_kick_removes_a_member(): void
    {
        $this->fakeClient();

        $this->deleteJson('/api/lcu/lobby/members/111111')
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::assertSent(
            fn ($request) => $request->method() === 'POST'
                && $request->url() === 'https://127.0.0.1:51705/lol-lobby/v2/lobby/members/111111/kick'
        );
    }

    public function test_lobby_position_rejects_invalid_roles(): void
    {
        $this->fakeClient();

        $this->postJson('/api/lcu/lobby/members/987654/position/NOPE')
            ->assertStatus(422)
            ->assertJson(['ok' => false]);
    }

    public function test_lobby_ready_check_accept_is_forwarded(): void
    {
        Http::fake([
            'https://127.0.0.1:51705/lol-matchmaking/*' => Http::response(null, 200),
        ]);

        $this->postJson('/api/lcu/lobby/ready-check/accept')
            ->assertOk()
            ->assertJson(['ok' => true]);

        Http::assertSent(
            fn ($request) => $request->method() === 'POST'
                && $request->url() === 'https://127.0.0.1:51705/lol-matchmaking/v1/ready-check/accept'
        );
    }

    public function test_friends_endpoint_returns_the_normalized_friend_list(): void
    {
        $this->fakeClient();

        $this->getJson('/api/lcu/friends')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonPath('0.name', 'KatarinaMain')
            ->assertJsonPath('0.availability', 'chat')
            ->assertJsonPath('0.invitable', true)
            ->assertJsonPath('0.summonerId', '111111')
            ->assertJsonPath('2.availability', 'offline')
            ->assertJsonPath('2.invitable', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function lobbyPayload(int $queueId = 420): array
    {
        return [
            'gameConfig' => ['queueId' => $queueId, 'isCustom' => false],
            'canStartActivity' => false,
            'chat' => ['id' => 'chat-1'],
            'localMember' => ['summonerId' => 987654, 'position' => 'FILL', 'isLeader' => true],
            'members' => [
                ['summonerId' => 987654, 'puuid' => 'puuid-123', 'gameName' => 'Test Summoner', 'profileIconId' => 4567, 'summonerLevel' => 128, 'isLeader' => true, 'position' => 'FILL', 'ready' => false],
                ['summonerId' => 111111, 'puuid' => 'puuid-kat', 'gameName' => 'KatarinaMain', 'profileIconId' => 1234, 'summonerLevel' => 55, 'isLeader' => false, 'position' => 'TOP', 'ready' => true],
                ['summonerId' => 222222, 'puuid' => 'puuid-darius', 'gameName' => 'Darius Bot', 'profileIconId' => 2345, 'summonerLevel' => 60, 'isLeader' => false, 'position' => 'JUNGLE', 'ready' => false],
            ],
        ];
    }

    private function fakeClient(array $overrides = []): void
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
            'https://127.0.0.1:51705/lol-inventory/*' => Http::response([
                'rp' => 1540,
                'ip' => 23480,
            ], 200),
            'https://127.0.0.1:51705/lol-lobby/v2/lobby' => Http::response($this->lobbyPayload(), 200),
            'https://127.0.0.1:51705/lol-chat/*' => Http::response([
                ['name' => 'KatarinaMain', 'availability' => 'chat', 'icon' => 1234, 'summonerId' => '111111'],
                ['name' => 'Darius Bot', 'availability' => 'in-game', 'icon' => 2345, 'summonerId' => '222222'],
                ['name' => 'Support Diff', 'availability' => 'offline', 'icon' => 3456, 'summonerId' => '333333'],
            ], 200),
            'https://127.0.0.1:51705/lol-game-queues/*' => Http::response([
                'id' => 420,
                'name' => 'Ranked Solo',
                'description' => 'Solo/duo ranked queue.',
            ], 200),
            'https://127.0.0.1:51705/*' => Http::response(null, 200),
        ], $overrides));
    }
}
