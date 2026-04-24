<?php

namespace App\Http\Controllers\Api;

use App\DTOs\VendorDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Services\VendorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    use ApiResponseTrait;

    public function __construct(protected
        VendorService $vendorService,
        )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vendor::class);
        $vendors = $this->vendorService->getPaginatedVendors($request->query('per_page', 15));
        return $this->paginatedResponse(VendorResource::collection($vendors), 'messages.fetched');
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $this->authorize('create', Vendor::class);
        $dto = VendorDTO::fromRequest($request);
        $vendor = $this->vendorService->createVendor($dto);
        return $this->successResponse(new VendorResource($vendor), 'messages.created', 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        $this->authorize('view', $vendor);
        return $this->successResponse(new VendorResource($vendor), 'messages.fetched');
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $this->authorize('update', $vendor);
        $dto = VendorDTO::fromRequest($request);
        $updatedVendor = $this->vendorService->updateVendor($vendor->id, $dto);
        return $this->successResponse(new VendorResource($updatedVendor), 'messages.updated');
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $this->authorize('delete', $vendor);
        $this->vendorService->deleteVendor($vendor->id);
        return $this->successResponse(null, 'messages.deleted');
    }
}
