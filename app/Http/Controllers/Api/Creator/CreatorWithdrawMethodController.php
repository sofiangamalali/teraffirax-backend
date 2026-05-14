<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreatorWithdrawMethodRequest;
use App\Http\Requests\UpdateCreatorWithdrawMethodRequest;
use App\Http\Requests\SetDefaultWithdrawMethodRequest;
use App\Http\Resources\CreatorWithdrawMethodResource;
use App\Http\Resources\WithdrawMethodResource;
use App\Models\WithdrawMethod;
use App\Services\CreatorWithdrawMethodService;
use App\Services\WithdrawMethodService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CreatorWithdrawMethodController extends Controller
{
    use ApiResponseTrait;

    protected CreatorWithdrawMethodService $creatorService;
    protected WithdrawMethodService $methodService;

    public function __construct(
        CreatorWithdrawMethodService $creatorService,
        WithdrawMethodService $methodService
    ) {
        $this->creatorService = $creatorService;
        $this->methodService = $methodService;
    }

    /**
     * Get withdrawal methods available for the creator's country.
     */
    public function availableMethods(Request $request)
    {
        $methods = $this->methodService->getAllMethods($request->user()->country_id);

        return $this->successResponse(
            WithdrawMethodResource::collection($methods->load('fields')),
            'messages.fetched'
        );
    }

    /**
     * Display a listing of the creator's withdrawal methods.
     */
    public function index(Request $request)
    {
        $methods = $this->creatorService->getCreatorMethods($request->user()->id);

        return $this->successResponse(
            CreatorWithdrawMethodResource::collection($methods->load(['values', 'withdrawMethod'])),
            'messages.fetched'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCreatorWithdrawMethodRequest $request)
    {
        $data = $request->validated();
        $data['creator_id'] = $request->user()->id;

        $method = $this->creatorService->createMethod($data);
        return $this->successResponse(
            new CreatorWithdrawMethodResource($method),
            'messages.created',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $method = $this->creatorService->getMethodById((int) $id);

        if (!$method || $method->creator_id !== $request->user()->id) {
            return $this->errorResponse('messages.not_found', 404);
        }

        return $this->successResponse(
            new CreatorWithdrawMethodResource($method),
            'messages.fetched'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCreatorWithdrawMethodRequest $request, string $id)
    {
        $this->creatorService->getMethodById((int) $id);
        $updated = $this->creatorService->setDefaultMethod((int) $id);
        if (!$updated) {
            return $this->errorResponse('messages.failed_to_update', 500);
        }
        return $this->successResponse(null, 'messages.updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $method = $this->creatorService->getMethodById((int) $id);

        if (!$method || $method->creator_id !== $request->user()->id) {
            return $this->errorResponse('messages.not_found', 404);
        }

        // Check if there are any withdraw requests referencing this method
        if ($method->withdrawRequests()->exists()) {
            return $this->errorResponse(__('Cannot delete payment method because it has associated withdrawal requests.'), 400);
        }

        $deleted = $this->creatorService->deleteMethod((int) $id);

        if (!$deleted) {
            return $this->errorResponse('messages.failed_to_delete', 500);
        }

        return $this->successResponse(null, 'messages.deleted');
    }

    /**
     * Set the specified withdrawal method as default.
     */
    public function setDefault(SetDefaultWithdrawMethodRequest $request, string $id)
    {
        $updated = $this->creatorService->setDefaultMethod((int) $id);

        if (!$updated) {
            return $this->errorResponse('messages.failed_to_update', 500);
        }

        return $this->successResponse(
            new CreatorWithdrawMethodResource($this->creatorService->getMethodById((int) $id)),
            'messages.updated'
        );
    }
}
