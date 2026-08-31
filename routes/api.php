<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Geo\CityController;
use App\Http\Controllers\Api\V1\Geo\CountryController;
use App\Http\Controllers\Api\V1\Procurement\ProcurementRequests\ProcurementRequestController;
use App\Http\Controllers\Api\V1\Procurement\Projects\ProjectController;
use App\Http\Controllers\Api\V1\Procurement\Projects\ProjectQuickStoreController;
use App\Http\Controllers\Api\V1\Procurement\Projects\ZoneQuickStoreController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['ok' => true])->name('api.health');

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [MeController::class, 'show'])->name('auth.me');
        Route::post('auth/logout', [LogoutController::class, 'destroy'])->name('auth.logout');

        Route::get('locations', [CountryController::class, 'index'])
            ->middleware('permission:locations.view')
            ->name('locations.index');

        Route::get('countries/{country}', [CountryController::class, 'show'])
            ->middleware('permission:locations.view')
            ->name('countries.show');

        Route::post('countries', [CountryController::class, 'store'])
            ->middleware('permission:locations.manage')
            ->name('countries.store');

        Route::put('countries/{country}', [CountryController::class, 'update'])
            ->middleware('permission:locations.manage')
            ->name('countries.update');

        Route::patch('countries/{country}', [CountryController::class, 'update']);

        Route::delete('countries/{country}', [CountryController::class, 'destroy'])
            ->middleware('permission:locations.manage')
            ->name('countries.destroy');

        Route::get('cities', [CityController::class, 'index'])
            ->middleware('permission:locations.manage')
            ->name('cities.index');

        Route::post('cities', [CityController::class, 'store'])
            ->middleware('permission:locations.manage')
            ->name('cities.store');

        Route::get('cities/{city}', [CityController::class, 'show'])
            ->middleware('permission:locations.manage')
            ->name('cities.show');

        Route::put('cities/{city}', [CityController::class, 'update'])
            ->middleware('permission:locations.manage')
            ->name('cities.update');

        Route::patch('cities/{city}', [CityController::class, 'update']);

        Route::delete('cities/{city}', [CityController::class, 'destroy'])
            ->middleware('permission:locations.manage')
            ->name('cities.destroy');

        Route::post('projects/quick-store', [ProjectQuickStoreController::class, 'store'])
            ->middleware('permission:projects.create')
            ->name('projects.quick-store');

        Route::post('zones/quick-store', [ZoneQuickStoreController::class, 'store'])
            ->middleware('permission:projects.update')
            ->name('zones.quick-store');

        Route::get('projects', [ProjectController::class, 'index'])
            ->middleware('permission:projects.view')
            ->name('projects.index');

        Route::post('projects', [ProjectController::class, 'store'])
            ->middleware('permission:projects.create')
            ->name('projects.store');

        Route::get('projects/{project}', [ProjectController::class, 'show'])
            ->middleware('permission:projects.view')
            ->name('projects.show');

        Route::put('projects/{project}', [ProjectController::class, 'update'])
            ->middleware('permission:projects.update')
            ->name('projects.update');

        Route::patch('projects/{project}', [ProjectController::class, 'update']);

        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
            ->middleware('permission:projects.update')
            ->name('projects.destroy');

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
