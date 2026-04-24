<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CreatorController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\TrackingLinkController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\TelegramChannelController;
use App\Http\Controllers\Api\CreatorPaymentMethodController;

Route::prefix('auth')->group(function () {
    Route::get('registration-data', [AuthController::class, 'registrationData']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
    Route::middleware('auth.token')->group(
        function () {
            Route::post('logout', [AuthController::class, 'logout']);
        }
    );
});

Route::get('r/{slug}', [TrackingLinkController::class, 'redirect'])->name('tracking.redirect');



Route::apiResource('creators', CreatorController::class);
Route::apiResource('vendors', VendorController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('tracking-links', TrackingLinkController::class);
Route::apiResource('telegram-channels', TelegramChannelController::class);
Route::apiResource('payment-methods', PaymentMethodController::class);
Route::apiResource('creator-payment-methods', CreatorPaymentMethodController::class);
Route::apiResource('withdrawals', WithdrawalController::class)->only(['index', 'store', 'show']);
Route::apiResource('countries', CountryController::class);
Route::apiResource('categories', CategoryController::class);
Route::patch('withdrawals/{id}/status', [WithdrawalController::class, 'updateStatus']);

