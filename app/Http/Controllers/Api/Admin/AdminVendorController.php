<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Resources\VendorResource;
use App\Services\VendorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AdminVendorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected VendorService $vendorService,
    ) {
    }

    /**
     * Display a listing of active vendors.
     */
    public function all(): JsonResponse
    {
        $this->authorize('manage', Admin::class);

        $vendors = $this->vendorService->getActiveVendors();

        return $this->successResponse(VendorResource::collection($vendors), 'messages.fetched');
    }
}
