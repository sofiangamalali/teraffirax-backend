<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingLinkController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('r/{slug}', [TrackingLinkController::class, 'redirect'])->name('tracking.redirect');

// Creator Routes
Route::group([], __DIR__ . '/api/creator.php');

// Admin Routes
Route::prefix('admin')->group(__DIR__ . '/api/admin.php');

// Scraper Routes
Route::prefix('scraper')->group(__DIR__ . '/api/scraper.php');

