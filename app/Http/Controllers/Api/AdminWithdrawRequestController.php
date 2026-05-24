<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WithdrawRequestService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CompleteWithdrawalRequest;
use App\Http\Requests\RejectWithdrawalRequest;

class AdminWithdrawRequestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected WithdrawRequestService $service
    ) {
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $adminId = $request->user()->id; // Assuming user() is the admin/account_manager

        $withdrawRequest = $this->service->approveRequest($id, $adminId);

        return $this->successResponse($withdrawRequest, 'Withdrawal request approved successfully.');
    }

    public function complete(CompleteWithdrawalRequest $request, string $id): JsonResponse
    {
        $adminId = $request->user()->id;

        $withdrawRequest = $this->service->completeRequest(
            $id,
            $request->transfer_reference,
            $request->receipt_url,
            $adminId
        );

        return $this->successResponse($withdrawRequest, 'Withdrawal request completed successfully.');
    }

    public function reject(RejectWithdrawalRequest $request, string $id): JsonResponse
    {
        $adminId = $request->user()->id;

        $withdrawRequest = $this->service->rejectRequest(
            $id,
            $request->rejection_reason,
            $adminId
        );

        return $this->successResponse($withdrawRequest, 'Withdrawal request rejected successfully.');
    }
}
