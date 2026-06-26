<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\Admin\StoreAccountManagerRequest;
use App\Http\Requests\Admin\UpdateAccountManagerRequest;
use App\Http\Resources\AccountManagerResource;
use App\Services\AccountManagerService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAccountManagerController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AccountManagerService $accountManagerService,
    ) {}

    /**
     * Display a listing of account managers.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $perPage = $request->query('per_page', 15);
        $managers = $this->accountManagerService->getPaginated($perPage);

        return $this->paginatedResponse(AccountManagerResource::collection($managers), 'messages.fetched');
    }

    /**
     * Display a listing of all account managers for dropdowns.
     */
    public function all(): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $managers = $this->accountManagerService->getAll();

        return $this->successResponse(AccountManagerResource::collection($managers), 'messages.fetched');
    }

    /**
     * Store a newly created account manager.
     */
    public function store(StoreAccountManagerRequest $request): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $manager = $this->accountManagerService->create($request->validated());

        return $this->successResponse(new AccountManagerResource($manager), 'messages.created', 201);
    }

    /**
     * Display the specified account manager.
     */
    public function show(int $id): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $manager = $this->accountManagerService->getById($id);

        return $this->successResponse(new AccountManagerResource($manager), 'messages.fetched');
    }

    /**
     * Update the specified account manager.
     */
    public function update(UpdateAccountManagerRequest $request, int $id): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $manager = $this->accountManagerService->update($id, $request->validated());

        return $this->successResponse(new AccountManagerResource($manager), 'messages.updated');
    }

    /**
     * Remove the specified account manager.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $this->accountManagerService->delete($id);

        return $this->successResponse(null, 'messages.deleted');
    }
}
