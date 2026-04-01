<?php

/**
 * Web (session) routes. Keep JSON/token APIs in `routes/api.php` (e.g. with Laravel Sanctum later).
 */

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Geo\CityController;
use App\Http\Controllers\Geo\CountryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Procurement\Categories\CategoryController;
use App\Http\Controllers\Procurement\Vendors\VendorWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/categories/export', [CategoryController::class, 'export'])->name('categories.export');
    Route::get('/categories/import/template', [CategoryController::class, 'downloadTemplate'])->name('categories.import.template');
    Route::get('/categories/import', [CategoryController::class, 'importForm'])->name('categories.import.form');
    Route::post('/categories/import', [CategoryController::class, 'import'])->name('categories.import');

    Route::resource('categories', CategoryController::class);
    Route::get('/locations', [CountryController::class, 'index'])->name('locations.index');
    Route::resource('countries', CountryController::class)->except(['show']);
    Route::resource('cities', CityController::class)->except(['show']);

    Route::resource('vendors', VendorWebController::class)->except(['destroy']);
});
