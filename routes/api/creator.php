<?php

use App\Http\Controllers\Api\Creator\CreatorWithdrawMethodController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\CreatorAuthController;
use App\Http\Controllers\Api\Creator\ProfileController;
use App\Http\Controllers\Api\CreatorTrackingLinkController;
use App\Http\Controllers\Api\CreatorSocialIntegrationController;
use App\Http\Controllers\Api\CreatorContentTemplateController;

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
    // Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);

    // Withdraw Methods
    Route::get('withdraw-methods', [CreatorWithdrawMethodController::class, 'availableMethods']);
    Route::apiResource('my-withdraw-methods', CreatorWithdrawMethodController::class)->except(['update', 'show']);

    // Tracking Links
    Route::apiResource('tracking-links', CreatorTrackingLinkController::class)->only(['index', 'store']);

    // Social Integrations
    Route::apiResource('social-integrations', CreatorSocialIntegrationController::class)->only(['index']);

    // Content Templates
    Route::get('template-variables', [CreatorContentTemplateController::class, 'variables']);
    Route::apiResource('content-templates', CreatorContentTemplateController::class)->only(['index', 'update']);
});



