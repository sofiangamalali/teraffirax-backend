<?php

namespace App\Http\Controllers\Api\Creator;

use App\DTOs\CreatorDashboardDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Creator\CreatorDashboardRequest;
use App\Http\Resources\{CreatorWalletResource, CreatorGraphResource, CreatorDashboardVideoResource};
use App\Services\CreatorDashboardService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorDashboardController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CreatorDashboardService $dashboardService
    ) {
    }

    /**
     * Get the creator's wallet balances.
     */
    public function wallet(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getWalletData($request->user()->id);

        return $this->successResponse(
            [
                new CreatorWalletResource($data),
            ],
            'messages.fetched'
        );
    }

    /**
     * Get the creator's dashboard graph and footer metrics.
     */
    public function graph(CreatorDashboardRequest $request): JsonResponse
    {
        $dto = CreatorDashboardDTO::fromRequest($request);
        $data = $this->dashboardService->getGraphData($request->user()->id, $dto);

        return $this->successResponse(
            new CreatorGraphResource($data),
            'messages.fetched'
        );
    }

    /**
     * Get the creator's dashboard videos (paginated).
     */
    public function videos(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $videos = $this->dashboardService->getPaginatedVideos($request->user()->id, $perPage);

        return $this->paginatedResponse(
            CreatorDashboardVideoResource::collection($videos),
            'messages.fetched'
        );
    }

    /**
     * Get the available dashboard filters.
     */
    public function filters(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getFiltersData($request->user()->id);

        return $this->successResponse(
            $data,
            'messages.fetched'
        );
    }
}
