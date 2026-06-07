<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Resources\AdminCreatorResource;
use App\Services\AdminCreatorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCreatorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AdminCreatorService $adminCreatorService,
    ) {
    }

    /**
     * Display a listing of creators.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $creators = $this->adminCreatorService->getPaginatedCreators(
            $request->query('per_page', 15)
        );

        return $this->paginatedResponse(AdminCreatorResource::collection($creators), 'messages.fetched');
    }

    /**
     * Toggle active status of a creator.
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $creator = $this->adminCreatorService->toggleCreatorActive($id);
        return $this->successResponse(new AdminCreatorResource($creator), 'messages.updated');
    }
}
