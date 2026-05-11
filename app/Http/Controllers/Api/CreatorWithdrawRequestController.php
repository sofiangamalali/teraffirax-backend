<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawRequestResource;
use App\Services\WithdrawRequestService;
use App\Http\Requests\StoreWithdrawRequestRequest;
use App\Http\Requests\ListWithdrawRequestsRequest;
use App\DTOs\WithdrawRequestDTO;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class CreatorWithdrawRequestController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected WithdrawRequestService $withdrawRequestService
    ) {}

    /**
     * List requests with filters.
     */
    public function index(ListWithdrawRequestsRequest $request): JsonResponse
    {
        $creatorId = $request->user()->id;
        
        $filters = $request->validated();
        
        $requests = $this->withdrawRequestService->getPaginatedForCreator($creatorId, $filters);
        
        return $this->paginatedResponse(
            WithdrawRequestResource::collection($requests),
            'messages.fetched'
        );
    }

    /**
     * Create a withdrawal request.
     */
    public function withdraw(StoreWithdrawRequestRequest $request): JsonResponse
    {
        $creatorId = $request->user()->id;

        $dto = new WithdrawRequestDTO(
            creator_id: $creatorId,
            creator_withdraw_method_id: $request->creator_withdraw_method_id
        );

        $withdrawRequest = $this->withdrawRequestService->withdraw($dto);
        
        return $this->successResponse(
            new WithdrawRequestResource($withdrawRequest),
            'Withdrawal request created successfully.',
            201
        );
    }

    /**
     * Get options for dropdown filters (statuses and payment methods).
     */
    public function getFilterOptions(Request $request): JsonResponse
    {
        $creatorId = $request->user()->id;

        $options = $this->withdrawRequestService->getOptionsForCreator($creatorId);

        return $this->successResponse($options);
    }
}
