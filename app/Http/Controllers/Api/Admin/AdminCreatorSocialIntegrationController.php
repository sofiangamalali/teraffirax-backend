<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\DTOs\AdminSocialIntegrationDTO;
use App\Http\Requests\Admin\UpdateAdminSocialIntegrationRequest;
use App\Http\Resources\AdminSocialIntegrationResource;
use App\Services\AdminCreatorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCreatorSocialIntegrationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AdminCreatorService $adminCreatorService,
    ) {}

    /**
     * Display a listing of social integrations for a creator.
     */
    public function socialIntegrations(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $integrations = $this->adminCreatorService->getCreatorSocialIntegrations(
            $id,
            $request->query('per_page', 15)
        );

        return $this->paginatedResponse(AdminSocialIntegrationResource::collection($integrations), 'messages.fetched');
    }

    /**
     * Update a social integration for a creator.
     */
    public function updateSocialIntegration(UpdateAdminSocialIntegrationRequest $request, int $id, int $integrationId): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $dto = AdminSocialIntegrationDTO::fromRequest($request, $id);
        $integration = $this->adminCreatorService->updateSocialIntegration($id, $integrationId, $dto);

        return $this->successResponse(new AdminSocialIntegrationResource($integration), 'messages.updated');
    }
}
