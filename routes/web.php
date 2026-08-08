<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\LockfileSettingsController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ApiController::class, 'index']);

Route::view('/launcher-required', 'launcher-required')->name('launcher.required');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

Route::prefix('api')->group(function () {
    Route::prefix('lcu')->group(function () {
        Route::get('status', [ApiController::class, 'status'])->name('api.lcu.status');
        Route::get('lockfile', [LockfileSettingsController::class, 'show'])->name('api.lcu.lockfile');
        Route::post('lockfile', [LockfileSettingsController::class, 'store'])->name('api.lcu.lockfile.store');
        Route::delete('lockfile', [LockfileSettingsController::class, 'destroy'])->name('api.lcu.lockfile.destroy');
        Route::get('assets/{path}', [ApiController::class, 'asset'])->where('path', '.*')->name('api.lcu.asset');
    });
});
