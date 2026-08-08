<?php

namespace App\Http\Controllers;

use App\Services\LeagueClient\DashboardProvider;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(DashboardProvider $dashboard): View
    {
        $data = $dashboard->data();
        $data['asset'] = fn (string $path): string => route('api.lcu.asset', ['path' => $path]);

        return view('settings', $data);
    }
}
