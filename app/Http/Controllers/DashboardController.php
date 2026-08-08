<?php

namespace App\Http\Controllers;

use App\Services\LeagueClient\DashboardProvider;
use App\Services\LeagueClient\QueueProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Render the dashboard seeded with live data from the local client, or
     * redirect to the launcher-required page when the client is not running.
     */
    public function index(DashboardProvider $dashboard, QueueProvider $queues): View|RedirectResponse
    {
        $data = $dashboard->data();

        if (! $data['connected']) {
            return redirect()->route('launcher.required');
        }

        $data['asset'] = fn (string $path): string => route('api.lcu.asset', ['path' => $path]);
        $data['queueModes'] = $queues->dashboardModes();

        return view('dashboard', $data);
    }
}
