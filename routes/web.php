<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard');

Route::prefix('api')->group(function () {
    Route::prefix('lcu')->group(function () {
        Route::get('status', [ApiController::class, 'status'])->name('api.lcu.status');
    });
});
