<?php

namespace App\Http\Controllers;

use App\Services\LeagueClient\ClientNotRunningException;
use App\Services\LeagueClient\LeagueClientConnector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ApiController extends Controller
{
    public function __construct(protected LeagueClientConnector $client) {}

    /**
     * Report whether the local League of Legends client is reachable and,
     * when it is, the current gameflow phase.
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $gameflow = $this->client->request('GET', '/lol-gameflow/v1/gameflow-phase');

            return response()->json([
                'connected' => true,
                'gameflow' => $gameflow ?: 'None',
            ]);
        } catch (ClientNotRunningException $e) {
            return response()->json([
                'connected' => false,
                'error' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'connected' => false,
                'error' => 'The League client is not responding.',
            ]);
        }
    }
}
