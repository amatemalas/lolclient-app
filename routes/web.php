<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\LockfileSettingsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::view('/launcher-required', 'launcher-required')->name('launcher.required');

Route::get('/lobby', [LobbyController::class, 'index'])->name('lobby');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

Route::prefix('api')->group(function () {
    Route::prefix('lcu')->group(function () {
        Route::get('status', [ApiController::class, 'status'])->name('api.lcu.status');
        Route::get('lockfile', [LockfileSettingsController::class, 'show'])->name('api.lcu.lockfile');
        Route::post('lockfile', [LockfileSettingsController::class, 'store'])->name('api.lcu.lockfile.store');
        Route::delete('lockfile', [LockfileSettingsController::class, 'destroy'])->name('api.lcu.lockfile.destroy');
        Route::get('assets/{path}', [ApiController::class, 'asset'])->where('path', '.*')->name('api.lcu.asset');

        Route::get('friends', [FriendController::class, 'index'])->name('api.lcu.friends');

        Route::get('lobby', [LobbyController::class, 'show'])->name('api.lcu.lobby');
        Route::post('lobby', [LobbyController::class, 'store'])->name('api.lcu.lobby.store');
        Route::delete('lobby', [LobbyController::class, 'destroy'])->name('api.lcu.lobby.destroy');
        Route::post('lobby/matchmaking/search', [LobbyController::class, 'startMatchmaking'])->name('api.lcu.lobby.search.start');
        Route::delete('lobby/matchmaking/search', [LobbyController::class, 'stopMatchmaking'])->name('api.lcu.lobby.search.stop');
        Route::post('lobby/ready-check/accept', [LobbyController::class, 'acceptReadyCheck'])->name('api.lcu.lobby.ready.accept');
        Route::post('lobby/ready-check/decline', [LobbyController::class, 'declineReadyCheck'])->name('api.lcu.lobby.ready.decline');
        Route::post('lobby/members/{summonerId}/invite', [LobbyController::class, 'invite'])->name('api.lcu.lobby.invite');
        Route::delete('lobby/members/{summonerId}', [LobbyController::class, 'kick'])->name('api.lcu.lobby.kick');
        Route::post('lobby/members/{summonerId}/position/{position}', [LobbyController::class, 'setPosition'])->name('api.lcu.lobby.position');
    });
});
