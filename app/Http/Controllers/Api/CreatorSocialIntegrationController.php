<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialIntegrationResource;
use App\Models\SocialIntegration;
use App\Services\SocialIntegrationService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorSocialIntegrationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected SocialIntegrationService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SocialIntegration::class);

        $perPage = $request->query('per_page', 15);
        $integrations = $this->service->paginateForCreator($request->user()->id, $perPage);

        return $this->paginatedResponse(
            SocialIntegrationResource::collection($integrations),
            'messages.fetched'
        );
    }

}
