<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LockfileSettingsTest extends TestCase
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

    public function test_show_reports_the_resolved_path_and_source(): void
    {
        $this->getJson('/api/lcu/lockfile')
            ->assertOk()
            ->assertJson([
                'current' => $this->lockfile,
                'source' => 'env',
                'saved' => null,
            ]);
    }

    public function test_store_persists_a_valid_lockfile(): void
    {
        $this->postJson('/api/lcu/lockfile', ['path' => $this->lockfile])
            ->assertOk()
            ->assertJson([
                'saved' => $this->lockfile,
                'source' => 'saved',
            ]);

        $this->getJson('/api/lcu/lockfile')
            ->assertOk()
            ->assertJson([
                'current' => $this->lockfile,
                'source' => 'saved',
                'saved' => $this->lockfile,
            ]);
    }

    public function test_store_rejects_a_path_that_does_not_exist(): void
    {
        $this->postJson('/api/lcu/lockfile', ['path' => '/nonexistent/lockfile'])
            ->assertStatus(422);
    }

    public function test_store_rejects_a_file_with_invalid_contents(): void
    {
        file_put_contents($this->lockfile, 'not a lockfile');

        $this->postJson('/api/lcu/lockfile', ['path' => $this->lockfile])
            ->assertStatus(422);
    }

    public function test_destroy_clears_the_saved_path(): void
    {
        $this->postJson('/api/lcu/lockfile', ['path' => $this->lockfile])->assertOk();

        $this->deleteJson('/api/lcu/lockfile')
            ->assertOk()
            ->assertJson(['saved' => null]);
    }

    public function test_settings_page_renders_the_lockfile_form(): void
    {
        Http::fake();

        $this->get('/settings')
            ->assertOk()
            ->assertSee('Client path')
            ->assertSee('Lockfile path');
    }

    public function test_launcher_required_page_offers_lockfile_settings(): void
    {
        $this->get(route('launcher.required'))
            ->assertOk()
            ->assertSee('Client path')
            ->assertSee('Lockfile path');
    }
}
