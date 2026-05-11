<?php

namespace App\Http\Controllers\Api;

use App\DTOs\SettingDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\SettingService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected SettingService $settingService,
    ) {}

    /**
     * Display a listing of the resource by group.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);
        
        $group = $request->query('group', 'general');
        $settings = $this->settingService->allByGroup($group);

        return $this->successResponse($settings, 'messages.fetched');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSettingRequest $request): JsonResponse
    {
        $this->authorize('create', Setting::class);
        
        $dto = SettingDTO::fromRequest($request);
        $setting = $this->settingService->set($dto);

        return $this->successResponse(
            new SettingResource($setting),
            'messages.created',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return $this->errorResponse('Setting not found', 404);
        }

        $this->authorize('view', $setting);

        return $this->successResponse(
            new SettingResource($setting),
            'messages.fetched'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return $this->errorResponse('Setting not found', 404);
        }

        $this->authorize('update', $setting);

        $dto = new SettingDTO(
            key: $key,
            value: $request->input('value'),
            type: $request->input('type', $setting->type),
            group: $request->input('group', $setting->group),
            user_id: $request->user()?->id
        );

        $updatedSetting = $this->settingService->set($dto);

        return $this->successResponse(
            new SettingResource($updatedSetting),
            'messages.updated'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $key, Request $request): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return $this->errorResponse('Setting not found', 404);
        }

        $this->authorize('delete', $setting);

        $this->settingService->forget($key, $request->user()?->id);

        return $this->successResponse(null, 'messages.deleted');
    }
}
