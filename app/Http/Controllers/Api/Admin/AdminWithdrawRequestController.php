<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\Admin\UpdateAdminWithdrawRequestStatusRequest;
use App\Http\Resources\AdminWithdrawRequestResource;
use App\Services\WithdrawRequestService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminWithdrawRequestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected WithdrawRequestService $withdrawRequestService,
    ) {}

    /**
     * Display a listing of withdraw requests.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $filters = $request->only(['status', 'creator_id', 'start_date', 'end_date']);
        $perPage = $request->query('per_page', 15);

        $requests = $this->withdrawRequestService->getPaginated($filters, $perPage);

        return $this->paginatedResponse(AdminWithdrawRequestResource::collection($requests), 'messages.fetched');
    }

    /**
     * Update the status of a withdraw request.
     */
    public function updateStatus(UpdateAdminWithdrawRequestStatusRequest $request, string $id): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $status = $request->input('status');

        if ($status === 'completed') {
            $withdrawRequest = $this->withdrawRequestService->complete($id);
        } else { // rejected
            $withdrawRequest = $this->withdrawRequestService->reject($id);
        }

        $withdrawRequest->load('creator');

        return $this->successResponse(new AdminWithdrawRequestResource($withdrawRequest), 'messages.updated');
    }
}
