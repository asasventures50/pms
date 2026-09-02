<?php

use App\Http\Controllers\Api\V1\Access\Users\UserController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Geo\CityController;
use App\Http\Controllers\Api\V1\Geo\CountryController;
use App\Http\Controllers\Api\V1\Procurement\Categories\CategoryController;
use App\Http\Controllers\Api\V1\Procurement\Categories\CategoryQuickStoreController;
use App\Http\Controllers\Api\V1\Procurement\Categories\SubcategoryQuickStoreController;
use App\Http\Controllers\Api\V1\Procurement\ProcurementRequests\ProcurementRequestController;
use App\Http\Controllers\Api\V1\Procurement\ProcurementRequests\ProcurementRequestFlowController;
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

        Route::get('users', [UserController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('users.index');

        Route::post('users', [UserController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('users.store');

        Route::get('users/{user}', [UserController::class, 'show'])
            ->middleware('permission:users.view')
            ->name('users.show');

        Route::put('users/{user}', [UserController::class, 'update'])
            ->middleware('permission:users.update')
            ->name('users.update');

        Route::delete('users/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('users.destroy');

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

        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
            ->middleware('permission:projects.update')
            ->name('projects.destroy');

        Route::post('categories/quick-store', [CategoryQuickStoreController::class, 'store'])
            ->middleware('permission:categories.create')
            ->name('categories.quick-store');

        Route::post('subcategories/quick-store', [SubcategoryQuickStoreController::class, 'store'])
            ->middleware('permission:categories.create|procurement-requests.create')
            ->name('subcategories.quick-store');

        Route::get('categories/subcategories/{subcategory}/move-preview', [CategoryController::class, 'movePreview'])
            ->middleware('permission:categories.update')
            ->name('categories.subcategories.move-preview');

        Route::get('categories', [CategoryController::class, 'index'])
            ->middleware('permission:categories.view')
            ->name('categories.index');

        Route::post('categories', [CategoryController::class, 'store'])
            ->middleware('permission:categories.create')
            ->name('categories.store');

        Route::get('categories/{category}/vendor-links', [CategoryController::class, 'categoryVendorLinks'])
            ->middleware('permission:categories.view')
            ->name('categories.vendor-links');

        Route::get('categories/{category}/subcategories/{subcategory}/vendor-links', [CategoryController::class, 'subcategoryVendorLinks'])
            ->middleware('permission:categories.view')
            ->name('categories.subcategories.vendor-links');

        Route::get('categories/{category}', [CategoryController::class, 'show'])
            ->middleware('permission:categories.view')
            ->name('categories.show');

        Route::put('categories/{category}', [CategoryController::class, 'update'])
            ->middleware('permission:categories.update')
            ->name('categories.update');

        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
            ->middleware('permission:categories.update')
            ->name('categories.destroy');

        Route::put('vendor-categories/{vendor_category}/reassign', [CategoryController::class, 'reassignVendorLink'])
            ->middleware('permission:categories.update')
            ->name('vendor-categories.reassign');

        Route::delete('vendor-categories/{vendor_category}', [CategoryController::class, 'removeVendorLink'])
            ->middleware('permission:categories.update')
            ->name('vendor-categories.destroy');

        Route::get('my-procurement-requests/flow', [ProcurementRequestFlowController::class, 'index'])
            ->middleware('permission:procurement-requests.view|procurement-requests.view-own|procurement-requests.create|rfqs.view')
            ->name('procurement-requests.my-flow');

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

        Route::delete('procurement-requests/{procurement_request}', [ProcurementRequestController::class, 'destroy'])
            ->middleware('permission:procurement-requests.update')
            ->name('procurement-requests.destroy');
    });
});
