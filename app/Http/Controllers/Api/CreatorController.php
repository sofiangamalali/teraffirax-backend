<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorResource;
use App\Services\CreatorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected
        CreatorService $creatorService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {

        $creators = $this->creatorService->getPaginatedCreators($request->query('per_page', 15));
        return $this->paginatedResponse(CreatorResource::collection($creators), 'messages.fetched');
    }

}
