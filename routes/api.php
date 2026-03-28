<?php

/**
 * API routes (reserved for future use).
 *
 * When you add a separate frontend or mobile clients:
 * - Run: composer require laravel/sanctum && php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
 * - Add Sanctum middleware to `bootstrap/app.php` for the `api` group if using SPA cookie auth
 * - Protect JSON endpoints with `auth:sanctum` while keeping session-based web auth in `routes/web.php`
 */

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['ok' => true])->name('api.health');
