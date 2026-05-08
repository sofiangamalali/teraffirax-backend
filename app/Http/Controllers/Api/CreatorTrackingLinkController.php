<?php

namespace App\Http\Controllers\Api;

use App\Models\TrackingLink;
use Illuminate\Http\Request;
use App\DTOs\TrackingLinkDTO;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\TrackingLinkService;
use App\Http\Resources\TrackingLinkResource;
use App\Http\Requests\StoreTrackingLinkRequest;

class CreatorTrackingLinkController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected TrackingLinkService $linkService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $links = $this->linkService->getPaginatedLinks($perPage);
        return $this->paginatedResponse(TrackingLinkResource::collection($links), 'messages.fetched');
    }

    public function store(StoreTrackingLinkRequest $request): JsonResponse
    {
        $dto = TrackingLinkDTO::fromRequest($request);
        $link = $this->linkService->createTrackingLink($request->user()?->id, $dto);

        return $this->successResponse(new TrackingLinkResource($link), 'messages.created', 201);
    }

    public function show(TrackingLink $tracking_link): JsonResponse
    {
        return $this->successResponse(new TrackingLinkResource($tracking_link), 'messages.fetched');
    }
}
