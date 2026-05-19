<?php

/**
 * Web (session) routes. Keep JSON/token APIs in `routes/api.php` (e.g. with Laravel Sanctum later).
 */

use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Geo\CityController;
use App\Http\Controllers\Geo\CountryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Procurement\Categories\CategoryController;
use App\Http\Controllers\Procurement\Catalog\SubcategoryQuickStoreController;
use App\Http\Controllers\Procurement\PurchaseOrders\PurchaseOrderController;
use App\Http\Controllers\Procurement\Vendors\VendorWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:users.view',
            'create' => 'permission:users.create',
            'store' => 'permission:users.create',
            'edit' => 'permission:users.update',
            'update' => 'permission:users.update',
            'destroy' => 'permission:users.delete',
        ]);

    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:roles.view',
            'create' => 'permission:roles.create',
            'store' => 'permission:roles.create',
            'edit' => 'permission:roles.update',
            'update' => 'permission:roles.update',
            'destroy' => 'permission:roles.delete',
        ]);

    Route::get('/categories/export', [CategoryController::class, 'export'])
        ->middleware('permission:categories.export')
        ->name('categories.export');

    Route::get('/categories/import/template', [CategoryController::class, 'downloadTemplate'])
        ->middleware('permission:categories.import')
        ->name('categories.import.template');

    Route::get('/categories/import', [CategoryController::class, 'importForm'])
        ->middleware('permission:categories.import')
        ->name('categories.import.form');

    Route::post('/categories/import', [CategoryController::class, 'import'])
        ->middleware('permission:categories.import')
        ->name('categories.import');

    Route::resource('categories', CategoryController::class)
        ->middleware([
            'index' => 'permission:categories.view',
            'show' => 'permission:categories.view',
            'create' => 'permission:categories.create',
            'store' => 'permission:categories.create',
            'edit' => 'permission:categories.update',
            'update' => 'permission:categories.update',
            'destroy' => 'permission:categories.update',
        ]);

    Route::post('/subcategories/quick-store', [SubcategoryQuickStoreController::class, 'quickStore'])
        ->middleware('permission:categories.create')
        ->name('subcategories.quick-store');

    Route::get('/locations', [CountryController::class, 'index'])
        ->middleware('permission:locations.view')
        ->name('locations.index');

    Route::resource('countries', CountryController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:locations.manage',
            'create' => 'permission:locations.manage',
            'store' => 'permission:locations.manage',
            'edit' => 'permission:locations.manage',
            'update' => 'permission:locations.manage',
            'destroy' => 'permission:locations.manage',
        ]);

    Route::resource('cities', CityController::class)
        ->except(['show'])
        ->middleware([
            'index' => 'permission:locations.manage',
            'create' => 'permission:locations.manage',
            'store' => 'permission:locations.manage',
            'edit' => 'permission:locations.manage',
            'update' => 'permission:locations.manage',
            'destroy' => 'permission:locations.manage',
        ]);

    Route::resource('vendors', VendorWebController::class)
        ->except(['destroy'])
        ->middleware([
            'index' => 'permission:vendors.view',
            'show' => 'permission:vendors.view',
            'create' => 'permission:vendors.create',
            'store' => 'permission:vendors.create',
            'edit' => 'permission:vendors.update',
            'update' => 'permission:vendors.update',
        ]);

    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->middleware([
            'index' => 'permission:purchase-orders.view',
            'show' => 'permission:purchase-orders.view',
            'create' => 'permission:purchase-orders.create',
            'store' => 'permission:purchase-orders.create',
            'edit' => 'permission:purchase-orders.update',
            'update' => 'permission:purchase-orders.update',
            'destroy' => 'permission:purchase-orders.update',
        ]);
});
