<?php

namespace App\Http\Controllers\Api\Creator;

use App\DTOs\CommissionFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListCommissionRequest;
use App\Http\Resources\CommissionResource;
use App\Services\CommissionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Models\Commission;

class CommissionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CommissionService $commissionService,
    ) {}

    /**
     * Display a listing of the creator's commissions.
     */
    public function index(ListCommissionRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Commission::class);

        $filters = CommissionFilterDTO::fromRequest($request);
        $perPage = $request->input('per_page', 15);

        $commissions = $this->commissionService->getCreatorCommissions(
            $request->user()->id,
            $filters,
            $perPage
        );

        return $this->paginatedResponse(
            CommissionResource::collection($commissions),
            'messages.fetched'
        );
    }
}
