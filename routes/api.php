<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['ok' => true])->name('api.health');

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [MeController::class, 'show'])->name('auth.me');
        Route::post('auth/logout', [LogoutController::class, 'destroy'])->name('auth.logout');
    });
});
