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
}
