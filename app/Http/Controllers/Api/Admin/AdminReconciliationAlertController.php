<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Resources\AdminReconciliationAlertResource;
use App\Repositories\Interfaces\ReconciliationAlertRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReconciliationAlertController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ReconciliationAlertRepositoryInterface $alertRepository,
    ) {
    }

    /**
     * Display a listing of reconciliation alerts.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $filters = $request->only(['creator_id', 'start_date', 'end_date']);
        $perPage = $request->query('per_page', 15);

        $alerts = $this->alertRepository->getPaginated($filters, $perPage);

        return $this->paginatedResponse(AdminReconciliationAlertResource::collection($alerts), 'messages.fetched');
    }
}
