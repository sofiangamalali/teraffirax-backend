<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AdminAuthController;
use App\Http\Controllers\Api\Admin\{AdminCreatorController, AdminCreatorSocialIntegrationController, AdminCreatorVendorTrackingController, AdminWithdrawRequestController, AdminReconciliationAlertController, AdminAccountManagerController, AdminVendorController, AdminCommissionController};

Route::post('auth/login', [AdminAuthController::class, 'login']);

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('auth/logout', [AdminAuthController::class, 'logout']);

    Route::get('creators', [AdminCreatorController::class, 'index']);
    Route::patch('creators/{id}/toggle-active', [AdminCreatorController::class, 'toggleActive']);
    Route::patch('creators/{id}/assign-account-manager', [AdminCreatorController::class, 'assignAccountManager']);

    Route::get('creators/{id}/social-integrations', [AdminCreatorSocialIntegrationController::class, 'socialIntegrations']);
    Route::put('creators/{id}/social-integrations/{integration_id}', [AdminCreatorSocialIntegrationController::class, 'updateSocialIntegration']);

    Route::get('creators/{id}/vendor-trackings', [AdminCreatorVendorTrackingController::class, 'index']);
    Route::put('creators/{id}/vendor-trackings/{tracking_id}', [AdminCreatorVendorTrackingController::class, 'update']);

    Route::get('withdraw-requests', [AdminWithdrawRequestController::class, 'index']);
    Route::patch('withdraw-requests/{id}/status', [AdminWithdrawRequestController::class, 'updateStatus']);

    Route::get('reconciliation-alerts', [AdminReconciliationAlertController::class, 'index']);

    Route::get('account-managers', [AdminAccountManagerController::class, 'index']);
    Route::post('account-managers', [AdminAccountManagerController::class, 'store']);
    Route::get('account-managers/all', [AdminAccountManagerController::class, 'all']);
    Route::get('account-managers/{id}', [AdminAccountManagerController::class, 'show']);
    Route::put('account-managers/{id}', [AdminAccountManagerController::class, 'update']);
    Route::delete('account-managers/{id}', [AdminAccountManagerController::class, 'destroy']);

    Route::get('vendors/all', [AdminVendorController::class, 'all']);
    Route::post('commissions', [AdminCommissionController::class, 'store']);
});


