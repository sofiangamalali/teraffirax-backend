<?php

namespace App\Http\Controllers\Api\Creator;

use App\DTOs\CreatorCommissionFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListCreatorCommissionRequest;
use App\Http\Resources\CreatorCommissionResource;
use App\Services\CreatorCommissionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Models\Commission;

class CreatorCommissionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CreatorCommissionService $commissionService,
    ) {}

    /**
     * Display a listing of the creator's commissions.
     */
    public function index(ListCreatorCommissionRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Commission::class);

        $filters = CreatorCommissionFilterDTO::fromRequest($request);
        $perPage = $request->input('per_page', 15);

        $commissions = $this->commissionService->getCreatorCommissions(
            $request->user()->id,
            $filters,
            $perPage
        );

        return $this->paginatedResponse(
            CreatorCommissionResource::collection($commissions),
            'messages.fetched'
        );
    }
}
