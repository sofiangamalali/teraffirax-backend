<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CreatorController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WithdrawalController;

Route::apiResource('creators', CreatorController::class);
Route::apiResource('vendors', VendorController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('countries', CountryController::class);
Route::apiResource('categories', CategoryController::class);

Route::patch('withdrawals/{id}/status', [WithdrawalController::class, 'updateStatus']);
