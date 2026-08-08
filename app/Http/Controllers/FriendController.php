<?php

namespace App\Http\Controllers;

use App\Services\LeagueClient\FriendProvider;
use Illuminate\Http\JsonResponse;

class FriendController extends Controller
{
    public function __construct(protected FriendProvider $friends) {}

    /**
     * Normalized friend list, polled by the lobby page to keep invites and
     * availability up to date.
     */
    public function index(): JsonResponse
    {
        return response()->json($this->friends->list());
    }
}
