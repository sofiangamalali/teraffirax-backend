<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CreatorPaymentMethodService;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreCreatorPaymentMethodRequest;
use App\Http\Requests\UpdateCreatorPaymentMethodRequest;

class CreatorPaymentMethodController extends Controller
{
    use ApiResponseTrait;
    protected CreatorPaymentMethodService $service;

    public function __construct(CreatorPaymentMethodService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('creator_id')) {
            $links = $this->service->getCreatorMethods((int)$request->creator_id);
        } else {
            $links = $this->service->getAllLinks();
        }
        return $this->successResponse($links, 'messages.fetched');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCreatorPaymentMethodRequest $request)
    {
        $link = $this->service->createLink($request->validated());
        return $this->successResponse($link, 'messages.created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $link = $this->service->getLinkById((int)$id);
        if (!$link) {
            return $this->errorResponse('messages.not_found', 404);
        }
        return $this->successResponse($link->load(['creator', 'paymentMethod']), 'messages.fetched');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCreatorPaymentMethodRequest $request, string $id)
    {
        $updated = $this->service->updateLink((int)$id, $request->validated());
        if (!$updated) {
            return $this->errorResponse('messages.not_found', 404);
        }

        return $this->successResponse($this->service->getLinkById((int)$id), 'messages.updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->service->deleteLink((int)$id);
        if (!$deleted) {
            return $this->errorResponse('messages.not_found', 404);
        }
        return $this->successResponse(null, 'messages.deleted');
    }
}
