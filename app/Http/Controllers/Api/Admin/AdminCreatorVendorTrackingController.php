<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\DTOs\AdminCreatorVendorTrackingDTO;
use App\Http\Requests\Admin\UpdateAdminCreatorVendorTrackingRequest;
use App\Http\Resources\AdminCreatorVendorTrackingResource;
use App\Services\AdminCreatorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCreatorVendorTrackingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AdminCreatorService $adminCreatorService,
    ) {}

    /**
     * Display a listing of vendor trackings for a creator.
     */
    public function index(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $trackings = $this->adminCreatorService->getCreatorVendorTrackings(
            $id,
            $request->query('per_page', 15)
        );

        return $this->paginatedResponse(AdminCreatorVendorTrackingResource::collection($trackings), 'messages.fetched');
    }

    /**
     * Update a vendor tracking record for a creator.
     */
    public function update(UpdateAdminCreatorVendorTrackingRequest $request, int $id, int $trackingId): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $dto = AdminCreatorVendorTrackingDTO::fromRequest($request, $id);
        $tracking = $this->adminCreatorService->updateCreatorVendorTracking($id, $trackingId, $dto);

        return $this->successResponse(new AdminCreatorVendorTrackingResource($tracking), 'messages.updated');
    }
}
