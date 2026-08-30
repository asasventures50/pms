<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Procurement\ProcurementRequests\ProcurementRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['ok' => true])->name('api.health');

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [MeController::class, 'show'])->name('auth.me');
        Route::post('auth/logout', [LogoutController::class, 'destroy'])->name('auth.logout');

        Route::get('procurement-requests', [ProcurementRequestController::class, 'index'])
            ->middleware('permission:procurement-requests.view|procurement-requests.view-own')
            ->name('procurement-requests.index');

        Route::post('procurement-requests', [ProcurementRequestController::class, 'store'])
            ->middleware('permission:procurement-requests.create')
            ->name('procurement-requests.store');

        Route::get('procurement-requests/{procurement_request}', [ProcurementRequestController::class, 'show'])
            ->middleware('permission:procurement-requests.view|procurement-requests.view-own')
            ->name('procurement-requests.show');

        Route::put('procurement-requests/{procurement_request}', [ProcurementRequestController::class, 'update'])
            ->middleware('permission:procurement-requests.update')
            ->name('procurement-requests.update');

        Route::patch('procurement-requests/{procurement_request}', [ProcurementRequestController::class, 'update']);

        Route::delete('procurement-requests/{procurement_request}', [ProcurementRequestController::class, 'destroy'])
            ->middleware('permission:procurement-requests.update')
            ->name('procurement-requests.destroy');
    });
});
