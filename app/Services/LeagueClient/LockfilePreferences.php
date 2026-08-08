<?php

namespace App\Services\LeagueClient;

use Illuminate\Support\Facades\Cache;
use Native\Desktop\Facades\Settings;

/**
 * Persist the user-chosen lockfile path. Runs on the NativePHP settings
 * store (electron-store) when the app is running natively, and falls back
 * to the Laravel cache so the setting still works during local web dev.
 */
class LockfilePreferences
{
    private const KEY = 'leagueclient.lockfile_path';

    public function get(): ?string
    {
        $value = $this->runningNatively()
            ? Settings::get(self::KEY)
            : Cache::get(self::KEY);

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function set(string $path): void
    {
        if ($this->runningNatively()) {
            Settings::set(self::KEY, $path);

            return;
        }

        Cache::forever(self::KEY, $path);
    }

    public function forget(): void
    {
        if ($this->runningNatively()) {
            Settings::forget(self::KEY);

            return;
        }

        Cache::forget(self::KEY);
    }

    private function runningNatively(): bool
    {
        return (bool) config('nativephp-internal.running');
    }
}
