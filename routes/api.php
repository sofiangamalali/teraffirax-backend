<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TrackingLinkController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('r/{slug}', RedirectController::class);

// Creator Routes
Route::group([], __DIR__ . '/api/creator.php');

// Admin Routes
Route::prefix('admin')->group(__DIR__ . '/api/admin.php');

// Scraper Routes
Route::prefix('scraper')->group(__DIR__ . '/api/scraper.php');

