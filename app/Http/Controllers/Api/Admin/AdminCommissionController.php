<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\Admin\StoreManualCommissionRequest;
use App\Http\Resources\CreatorCommissionResource;
use App\Services\AdminCommissionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AdminCommissionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AdminCommissionService $adminCommissionService,
    ) {
    }

    /**
     * Store a manually entered commission.
     */
    public function store(StoreManualCommissionRequest $request): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $commission = $this->adminCommissionService->createManualCommission($request->validated());

        return $this->successResponse(new CreatorCommissionResource($commission), 'messages.created', 201);
    }
}
