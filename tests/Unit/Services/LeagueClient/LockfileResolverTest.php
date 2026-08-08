<?php

namespace Tests\Unit\Services\LeagueClient;

use App\Services\LeagueClient\LockfileResolver;
use Tests\TestCase;

class LockfileResolverTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/lockfile_resolver_'.uniqid();

        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir.'/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->dir);

        parent::tearDown();
    }

    public function test_it_resolves_the_configured_path(): void
    {
        $path = $this->makeLockfile('configured');

        config(['leagueclient.lockfile_path' => $path]);

        $this->assertSame($path, (new LockfileResolver)->resolve());
    }

    public function test_an_override_takes_precedence_over_the_configured_path(): void
    {
        $configured = $this->makeLockfile('configured');
        $override = $this->makeLockfile('override');

        config(['leagueclient.lockfile_path' => $configured]);

        $this->assertSame($override, (new LockfileResolver)->resolve($override));
    }

    public function test_it_falls_back_to_platform_defaults(): void
    {
        $path = $this->makeLockfile('installed');

        $resolver = new LockfileResolver([
            'lockfile_path' => null,
            'lockfile_paths' => [strtolower(PHP_OS_FAMILY) => [$path]],
        ]);

        $this->assertSame($path, $resolver->resolve());
    }

    public function test_it_returns_null_when_nothing_is_found(): void
    {
        $resolver = new LockfileResolver([
            'lockfile_path' => null,
            'lockfile_paths' => [],
            'lockfile_runtime_detection' => false,
        ]);

        $this->assertNull($resolver->resolve());
    }

    public function test_candidates_are_ordered_and_deduplicated(): void
    {
        $path = $this->makeLockfile('dedupe');

        $resolver = new LockfileResolver([
            'lockfile_path' => $path,
            'lockfile_paths' => [strtolower(PHP_OS_FAMILY) => [$path, $path.'-other']],
            'lockfile_runtime_detection' => false,
        ]);

        $this->assertSame([$path, $path.'-other'], $resolver->candidates());
    }

    private function makeLockfile(string $name): string
    {
        $path = $this->dir.'/'.$name;

        file_put_contents($path, 'LeagueClient:1234:51705:secret-token:https');

        return $path;
    }
}
