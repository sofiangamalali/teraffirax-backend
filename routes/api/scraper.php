<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Scraper\ScraperController;


Route::middleware('auth.scraper')->group(function () {
    Route::get('health', [ScraperController::class, 'health']);
    Route::get('vendors/{slug}/categories', [ScraperController::class, 'vendorCategories']);
    Route::post('products/upsert', [ScraperController::class, 'upsertProducts']);
});
