<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Services\WithdrawalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWithdrawalRequest;
use App\Http\Requests\UpdateWithdrawalStatusRequest;

class WithdrawalController extends Controller
{
    use ApiResponseTrait;
    protected WithdrawalService $service;

    public function __construct(WithdrawalService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('creator_id')) {
            $withdrawals = $this->service->getCreatorWithdrawals((int)$request->creator_id);
        }
        else {
            $withdrawals = $this->service->getAllWithdrawals();
        }
        return $this->successResponse($withdrawals, 'messages.fetched');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWithdrawalRequest $request)
    {
        $withdrawal = $this->service->createWithdrawal($request->validated());
        return $this->successResponse($withdrawal, 'messages.created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $withdrawal = $this->service->getWithdrawalById((int)$id);
        if (!$withdrawal) {
            return $this->errorResponse('messages.not_found', 404);
        }
        return $this->successResponse($withdrawal, 'messages.fetched');
    }

    /**
     * Update status.
     */
    public function updateStatus(UpdateWithdrawalStatusRequest $request, string $id)
    {
        $updated = $this->service->updateStatus((int)$id, $request->validated()['status']);
        if (!$updated) {
            return $this->errorResponse('messages.not_found', 404);
        }

        return $this->successResponse($this->service->getWithdrawalById((int)$id), 'messages.updated');
    }

    public function update(Request $request, string $id)
    {
        return $this->errorResponse('messages.method_not_allowed', 405);
    }

    public function destroy(string $id)
    {
        return $this->errorResponse('messages.method_not_allowed', 405);
    }
}
