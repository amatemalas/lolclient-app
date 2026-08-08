<?php

namespace App\Http\Controllers;

use App\Services\LeagueClient\LockfilePreferences;
use App\Services\LeagueClient\LockfileResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LockfileSettingsController extends Controller
{
    public function __construct(protected LockfilePreferences $preferences) {}

    /**
     * Report the resolved lockfile path, where it came from, and every
     * candidate path considered.
     */
    public function show(): JsonResponse
    {
        return response()->json($this->payload(new LockfileResolver));
    }

    /**
     * Validate and persist a user-chosen lockfile path.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = trim($data['path']);

        if (! $this->isValidLockfile($path)) {
            return response()->json([
                'error' => 'That is not a readable League of Legends lockfile.',
            ], 422);
        }

        $this->preferences->set($path);

        config(['leagueclient.lockfile_path' => $path]);

        return response()->json($this->payload(new LockfileResolver, $path));
    }

    /**
     * Clear a previously saved path and fall back to auto-detection.
     */
    public function destroy(): JsonResponse
    {
        $this->preferences->forget();

        config(['leagueclient.lockfile_path' => null]);

        return response()->json($this->payload(new LockfileResolver));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(LockfileResolver $resolver, ?string $override = null): array
    {
        $saved = $this->preferences->get();
        $configured = config('leagueclient.lockfile_path');
        $configured = is_string($configured) && $configured !== '' ? $configured : null;
        $resolved = $resolver->resolve($override);

        $source = match (true) {
            $saved !== null => 'saved',
            $configured !== null => 'env',
            $resolved !== null => 'detected',
            default => null,
        };

        return [
            'current' => $resolved,
            'source' => $source,
            'saved' => $saved,
            'candidates' => $resolver->candidates($override),
        ];
    }

    /**
     * A path is only accepted when it currently holds a parseable LCU
     * lockfile, so typos and stale installs are caught immediately.
     */
    private function isValidLockfile(string $path): bool
    {
        if (! is_file($path) || ! is_readable($path)) {
            return false;
        }

        $contents = trim((string) file_get_contents($path));

        return preg_match('/^([^:]+):(\d+):(\d+):([^:]+):(\w+)$/', $contents) === 1;
    }
}
