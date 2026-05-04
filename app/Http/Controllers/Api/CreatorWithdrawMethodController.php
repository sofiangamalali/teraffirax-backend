<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CreatorWithdrawMethodService;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreCreatorWithdrawMethodRequest;
use App\Http\Requests\UpdateCreatorWithdrawMethodRequest;
use App\Http\Resources\CreatorWithdrawMethodResource;

class CreatorWithdrawMethodController extends Controller
{
    use ApiResponseTrait;
    protected CreatorWithdrawMethodService $service;

    public function __construct(CreatorWithdrawMethodService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('creator_id')) {
            $methods = $this->service->getCreatorMethods((int)$request->creator_id);
        } else {
            $methods = $this->service->getAllMethods();
        }
        return $this->successResponse(CreatorWithdrawMethodResource::collection($methods->load(['values', 'withdrawMethod'])), 'messages.fetched');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCreatorWithdrawMethodRequest $request)
    {
        $method = $this->service->createMethod($request->validated());
        return $this->successResponse(new CreatorWithdrawMethodResource($method), 'messages.created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $method = $this->service->getMethodById((int)$id);
        if (!$method) {
            return $this->errorResponse('messages.not_found', 404);
        }
        return $this->successResponse(new CreatorWithdrawMethodResource($method->load(['values', 'withdrawMethod'])), 'messages.fetched');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCreatorWithdrawMethodRequest $request, string $id)
    {
        $updated = $this->service->updateMethod((int)$id, $request->validated());
        if (!$updated) {
            return $this->errorResponse('messages.not_found', 404);
        }

        return $this->successResponse($this->service->getMethodById((int)$id), 'messages.updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->service->deleteMethod((int)$id);
        if (!$deleted) {
            return $this->errorResponse('messages.not_found', 404);
        }
        return $this->successResponse(null, 'messages.deleted');
    }
}
