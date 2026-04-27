<?php

namespace App\Http\Controllers\Api\Creator;

use App\DTOs\CreatorDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Creator\UpdateProfileRequest;
use App\Http\Resources\CreatorProfileResource;
use App\Services\CreatorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CreatorService $creatorService
    ) {
    }

    /**
     * Display the authenticated creator's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $creator = $this->creatorService->getCreatorById($request->user()->id);

        return $this->successResponse(
            new CreatorProfileResource($creator->load(['country', 'categories', 'accountManager'])),
            'messages.fetched'
        );
    }

    /**
     * Update the authenticated creator's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $creator = $this->creatorService->updateCreator(
            $request->user()->id,
            CreatorDTO::fromRequest($request)
        );

        return $this->successResponse(
            new CreatorProfileResource($creator->load(['country', 'categories', 'accountManager'])),
            'messages.updated'
        );
    }
}
