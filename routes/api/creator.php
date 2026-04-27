<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\CreatorAuthController;
use App\Http\Controllers\Api\Creator\ProfileController;

Route::prefix('auth')->group(function () {
    Route::get('registration-data', [CreatorAuthController::class, 'registrationData']);
    Route::post('register', [CreatorAuthController::class, 'register']);
    Route::post('login', [CreatorAuthController::class, 'login']);
    Route::post('forgot-password', [CreatorAuthController::class, 'forgotPassword']);
    Route::post('verify-otp', [CreatorAuthController::class, 'verifyOtp']);
    Route::post('reset-password', [CreatorAuthController::class, 'resetPassword']);
    Route::post('resend-otp', [CreatorAuthController::class, 'resendOtp']);
    Route::post('refresh-token', [CreatorAuthController::class, 'refreshToken']);

    Route::middleware(['auth'])->group(function () {
        Route::post('logout', [CreatorAuthController::class, 'logout']);
    });
});

Route::middleware(['auth', 'creator.active'])->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
});




