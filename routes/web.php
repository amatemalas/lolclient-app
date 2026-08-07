<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ApiController::class, 'index']);

Route::view('/launcher-required', 'launcher-required')->name('launcher.required');

Route::prefix('api')->group(function () {
    Route::prefix('lcu')->group(function () {
        Route::get('status', [ApiController::class, 'status'])->name('api.lcu.status');
        Route::get('assets/{path}', [ApiController::class, 'asset'])->where('path', '.*')->name('api.lcu.asset');
    });
});
