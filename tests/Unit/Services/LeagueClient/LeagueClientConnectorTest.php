<?php

namespace Tests\Unit\Services\LeagueClient;

use App\Services\LeagueClient\ClientNotRunningException;
use App\Services\LeagueClient\LeagueClientConnector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LeagueClientConnectorTest extends TestCase
{
    private string $lockfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lockfile = tempnam(sys_get_temp_dir(), 'lockfile_');

        config([
            'leagueclient.lockfile_path' => $this->lockfile,
            'leagueclient.timeout' => 3,
            'leagueclient.connect_timeout' => 2,
        ]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->lockfile)) {
            unlink($this->lockfile);
        }

        parent::tearDown();
    }

    public function test_it_parses_credentials_from_the_lockfile(): void
    {
        file_put_contents($this->lockfile, 'LeagueClient:1234:51705:AbC123_xYz-9:https');

        $this->assertSame([
            'pid' => 1234,
            'port' => 51705,
            'token' => 'AbC123_xYz-9',
            'protocol' => 'https',
        ], (new LeagueClientConnector)->credentials());
    }

    public function test_it_throws_when_no_lockfile_exists(): void
    {
        config(['leagueclient.lockfile_path' => '/nonexistent/lockfile']);

        $this->expectException(ClientNotRunningException::class);

        (new LeagueClientConnector)->credentials();
    }

    public function test_it_authenticates_requests_against_the_local_client(): void
    {
        file_put_contents($this->lockfile, 'LeagueClient:1234:51705:secret-token:https');

        Http::fake([
            'https://127.0.0.1:51705/*' => Http::response('"Lobby"', 200, ['Content-Type' => 'application/json']),
        ]);

        $phase = (new LeagueClientConnector)->request('GET', '/lol-gameflow/v1/gameflow-phase');

        $this->assertSame('Lobby', $phase);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://127.0.0.1:51705/lol-gameflow/v1/gameflow-phase'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('riot:secret-token'));
        });
    }
}
