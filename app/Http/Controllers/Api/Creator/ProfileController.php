<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Resources\CreatorProfileResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display the authenticated creator's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $creator = $request->user()->load(['country', 'categories', 'accountManager']);
        
        return $this->successResponse(
            new CreatorProfileResource($creator),
            'messages.fetched'
        );
    }

    /**
     * Update the authenticated creator's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $creator = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if (isset($validated['name'])) {
            $creator->update(['name' => $validated['name']]);
        }

        if (isset($validated['categories'])) {
            $creator->categories()->sync($validated['categories']);
        }

        return $this->successResponse(
            new CreatorProfileResource($creator->load(['country', 'categories', 'accountManager'])),
            'messages.updated'
        );
    }
}
