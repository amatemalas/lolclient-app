<?php

namespace App\Http\Controllers;

use App\Services\LeagueClient\DashboardProvider;
use App\Services\LeagueClient\FriendProvider;
use App\Services\LeagueClient\LobbyProvider;
use App\Services\LeagueClient\QueueProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LobbyController extends Controller
{
    public function __construct(protected LobbyProvider $lobby) {}

    /**
     * Render the lobby page seeded with the current client state.
     */
    public function index(DashboardProvider $dashboard, FriendProvider $friends, QueueProvider $queues): View|RedirectResponse
    {
        $data = array_merge($dashboard->shell(), $this->lobby->data());

        if (! $data['connected']) {
            return redirect()->route('launcher.required');
        }

        $data['asset'] = fn (string $path): string => route('api.lcu.asset', ['path' => $path]);
        $data['friends'] = $friends->list();
        $data['queueModes'] = $queues->dashboardModes();
        $data['positions'] = $queues->positions();

        return view('lobby', $data);
    }

    /**
     * JSON payload polled by the lobby page for live updates.
     */
    public function show(): JsonResponse
    {
        return response()->json($this->lobby->data());
    }

    /**
     * Switch the player into a lobby for the requested game mode.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'queue_id' => ['required', 'integer'],
        ]);

        $result = $this->lobby->switchTo((int) $data['queue_id']);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /**
     * Leave the current lobby.
     */
    public function destroy(): JsonResponse
    {
        return response()->json($this->lobby->leave());
    }

    /**
     * Start matchmaking for the current lobby.
     */
    public function startMatchmaking(): JsonResponse
    {
        return response()->json($this->lobby->startMatchmaking());
    }

    /**
     * Cancel matchmaking for the current lobby.
     */
    public function stopMatchmaking(): JsonResponse
    {
        return response()->json($this->lobby->stopMatchmaking());
    }

    /**
     * Invite a friend into the lobby.
     */
    public function invite(int $summonerId): JsonResponse
    {
        return response()->json($this->lobby->invite($summonerId));
    }

    /**
     * Kick a member from the lobby (owner only).
     */
    public function kick(int $summonerId): JsonResponse
    {
        return response()->json($this->lobby->kick($summonerId));
    }

    /**
     * Set a member's preferred position.
     */
    public function setPosition(int $summonerId, string $position): JsonResponse
    {
        $result = $this->lobby->setPosition($summonerId, $position);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /**
     * Accept an active ready check.
     */
    public function acceptReadyCheck(): JsonResponse
    {
        return response()->json($this->lobby->acceptReadyCheck());
    }

    /**
     * Decline an active ready check.
     */
    public function declineReadyCheck(): JsonResponse
    {
        return response()->json($this->lobby->declineReadyCheck());
    }
}
