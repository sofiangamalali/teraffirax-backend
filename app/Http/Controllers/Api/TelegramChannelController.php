<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TelegramChannelService;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreTelegramChannelRequest;
use App\Http\Requests\UpdateTelegramChannelRequest;

class TelegramChannelController extends Controller
{
    use ApiResponseTrait;
    protected TelegramChannelService $service;

    public function __construct(TelegramChannelService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $channels = $this->service->getAllChannels();
        return $this->successResponse($channels, 'messages.fetched');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTelegramChannelRequest $request)
    {
        $channel = $this->service->createChannel($request->validated());
        return $this->successResponse($channel, 'messages.created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $channel = $this->service->getChannelById((int)$id);
        if (!$channel) {
            return $this->errorResponse('messages.not_found', 404);
        }
        return $this->successResponse($channel, 'messages.fetched');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTelegramChannelRequest $request, string $id)
    {
        $updated = $this->service->updateChannel((int)$id, $request->validated());
        if (!$updated) {
            return $this->errorResponse('messages.not_found', 404);
        }

        return $this->successResponse($this->service->getChannelById((int)$id), 'messages.updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->service->deleteChannel((int)$id);
        if (!$deleted) {
            return $this->errorResponse('messages.not_found', 404);
        }
        return $this->successResponse(null, 'messages.deleted');
    }
}
