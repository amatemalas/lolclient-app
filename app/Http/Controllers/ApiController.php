<?php

namespace App\Http\Controllers;

use App\Services\LeagueClient\ClientNotRunningException;
use App\Services\LeagueClient\DashboardProvider;
use App\Services\LeagueClient\LeagueClientConnector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class ApiController extends Controller
{
    public function __construct(protected LeagueClientConnector $client) {}

    /**
     * Render the dashboard seeded with live data from the local client, or
     * redirect to the launcher-required page when the client is not running.
     */
    public function index(DashboardProvider $dashboard): View|RedirectResponse
    {
        $data = $dashboard->data();

        if (! $data['connected']) {
            return redirect()->route('launcher.required');
        }

        return view('dashboard', $data);
    }

    /**
     * Report whether the local client is reachable, for the live status pill.
     */
    public function status(): JsonResponse
    {
        try {
            $gameflow = $this->client->request('GET', '/lol-gameflow/v1/gameflow-phase');

            return response()->json([
                'connected' => true,
                'gameflow' => is_string($gameflow) && $gameflow !== '' ? $gameflow : 'None',
            ]);
        } catch (ClientNotRunningException $e) {
            return response()->json(['connected' => false, 'error' => $e->getMessage()]);
        } catch (Throwable) {
            return response()->json(['connected' => false, 'error' => 'The League client is not responding.']);
        }
    }

    /**
     * Proxy a static asset (profile icons, champion squares, ...) out of the
     * local client so the browser can render it without LCU credentials.
     */
    public function asset(string $path): JsonResponse|Response
    {
        $assetPath = '/lol-game-data/assets/'.ltrim($path, '/');

        try {
            $response = $this->client->rawClient()->get($assetPath);
        } catch (Throwable) {
            return response()->json(['error' => 'Asset unavailable.'], 404);
        }

        if (! $response->successful()) {
            return response()->json(['error' => 'Asset unavailable.'], $response->status());
        }

        $body = $response->body();

        return response($body, $response->status(), [
            'Content-Type' => $response->header('Content-Type', 'application/octet-stream'),
            'Content-Length' => (string) strlen($body),
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
