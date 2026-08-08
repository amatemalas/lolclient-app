<?php

namespace App\Services\LeagueClient;

use Throwable;

class LockfileResolver
{
    /**
     * @param  array<string, mixed>|null  $config
     */
    public function __construct(protected ?array $config = null)
    {
        $this->config ??= config('leagueclient');
    }

    /**
     * Resolve the path to a readable lockfile, or null when the client is not
     * running (or not installed in a known location).
     *
     * An explicitly configured path (env, saved setting, or override) is
     * authoritative: it is the only candidate probed, so a stale override is
     * reported as "not found" rather than silently ignored. Runtime detection
     * only runs when no explicit path has been set.
     */
    public function resolve(?string $override = null): ?string
    {
        $explicit = is_string($override) && $override !== ''
            ? $override
            : ($this->config['lockfile_path'] ?? null);

        if (is_string($explicit) && $explicit !== '') {
            return is_file($explicit) && is_readable($explicit) ? $explicit : null;
        }

        foreach ($this->candidates() as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Ordered, de-duplicated candidate paths: explicit override, configured
     * path, platform defaults, then anything we can detect at runtime.
     *
     * @return array<int, string>
     */
    public function candidates(?string $override = null): array
    {
        $paths = [];

        $push = function (string $path) use (&$paths): void {
            $path = trim($path);

            if ($path !== '' && ! in_array($path, $paths, true)) {
                $paths[] = $path;
            }
        };

        if (is_string($override) && $override !== '') {
            $push($override);
        }

        $configured = $this->config['lockfile_path'] ?? null;

        if (is_string($configured) && $configured !== '') {
            $push($configured);
        }

        foreach (($this->config['lockfile_paths'] ?? []) as $platform => $pathsForPlatform) {
            if (strtolower((string) $platform) === strtolower(PHP_OS_FAMILY)) {
                foreach ((array) $pathsForPlatform as $path) {
                    $push($path);
                }
            }
        }

        if (PHP_OS_FAMILY === 'Windows' && $this->runtimeDetectionEnabled()) {
            foreach ($this->windowsRuntimeCandidates() as $path) {
                $push($path);
            }
        }

        return $paths;
    }

    private function runtimeDetectionEnabled(): bool
    {
        return (bool) ($this->config['lockfile_runtime_detection'] ?? true);
    }

    /**
     * @return array<int, string>
     */
    private function windowsRuntimeCandidates(): array
    {
        $paths = [];

        foreach ($this->riotClientInstallDirs() as $dir) {
            $paths[] = rtrim($dir, '\\/').DIRECTORY_SEPARATOR.'Config'.DIRECTORY_SEPARATOR.'lockfile';
        }

        foreach ($this->leagueInstallDirs() as $dir) {
            $paths[] = rtrim($dir, '\\/').DIRECTORY_SEPARATOR.'lockfile';
        }

        return $paths;
    }

    /**
     * Parse the Riot Client install manifest for every Riot Client directory.
     *
     * @return array<int, string>
     */
    private function riotClientInstallDirs(): array
    {
        $programData = getenv('PROGRAMDATA');

        if (! is_string($programData) || $programData === '') {
            return [];
        }

        $manifest = rtrim($programData, '\\/').'\\Riot Games\\RiotClientInstalls.json';

        if (! is_file($manifest) || ! is_readable($manifest)) {
            return [];
        }

        try {
            $data = json_decode((string) file_get_contents($manifest), true);
        } catch (Throwable) {
            return [];
        }

        if (! is_array($data)) {
            return [];
        }

        $dirs = [];

        if (is_string($data['rc_default'] ?? null)) {
            $dirs[] = dirname($data['rc_default']);
        }

        foreach (($data['associated_client'] ?? []) as $client) {
            if (is_string($client)) {
                $dirs[] = dirname($client);
            }
        }

        return array_values(array_unique(array_filter($dirs)));
    }

    /**
     * Locate the League of Legends install directory from the registry.
     *
     * @return array<int, string>
     */
    private function leagueInstallDirs(): array
    {
        if (! function_exists('exec')) {
            return [];
        }

        $keys = [
            'HKLM\\SOFTWARE\\WOW6432Node\\Riot Games\\Riot Games Installer\\League of Legends',
            'HKLM\\SOFTWARE\\Riot Games\\Riot Games Installer\\League of Legends',
            'HKCU\\SOFTWARE\\Riot Games\\Riot Games Installer\\League of Legends',
        ];

        $dirs = [];

        foreach ($keys as $key) {
            $value = $this->registryInstallPath($key);

            if ($value !== null) {
                $dirs[] = $value;
            }
        }

        return array_values(array_unique(array_filter($dirs)));
    }

    private function registryInstallPath(string $key): ?string
    {
        $output = [];
        $exitCode = 1;

        @exec('reg query "'.$key.'" /v InstallPath 2>NUL', $output, $exitCode);

        if ($exitCode !== 0) {
            return null;
        }

        foreach ($output as $line) {
            if (preg_match('/InstallPath\s+REG_SZ\s+(.+)$/i', trim($line), $matches)) {
                return trim($matches[1], " \t\n\r\0\x0B\"");
            }
        }

        return null;
    }
}
