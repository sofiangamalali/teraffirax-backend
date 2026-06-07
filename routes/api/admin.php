<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AdminAuthController;
use App\Http\Controllers\Api\Admin\{AdminCreatorController, AdminCreatorSocialIntegrationController};

Route::post('auth/login', [AdminAuthController::class, 'login']);

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('auth/logout', [AdminAuthController::class, 'logout']);

    Route::get('creators', [AdminCreatorController::class, 'index']);
    Route::patch('creators/{id}/toggle-active', [AdminCreatorController::class, 'toggleActive']);

    Route::get('creators/{id}/social-integrations', [AdminCreatorSocialIntegrationController::class, 'socialIntegrations']);
    Route::put('creators/{id}/social-integrations/{integration_id}', [AdminCreatorSocialIntegrationController::class, 'updateSocialIntegration']);
});


