<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WithdrawMethodService;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreWithdrawMethodRequest;
use App\Http\Requests\UpdateWithdrawMethodRequest;
use App\Http\Resources\WithdrawMethodResource;

class WithdrawMethodController extends Controller
{
    use ApiResponseTrait;
    protected WithdrawMethodService $service;

    public function __construct(WithdrawMethodService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $countryId = $request->has('country_id') ? (int)$request->country_id : null;
        $methods = $this->service->getAllMethods($countryId);
        return $this->successResponse(WithdrawMethodResource::collection($methods->load('fields')), 'messages.fetched');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWithdrawMethodRequest $request)
    {
        $method = $this->service->createMethod($request->validated());
        return $this->successResponse(new WithdrawMethodResource($method), 'messages.created', 201);
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
        return $this->successResponse(new WithdrawMethodResource($method->load('fields')), 'messages.fetched');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWithdrawMethodRequest $request, string $id)
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
