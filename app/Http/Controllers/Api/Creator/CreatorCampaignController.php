<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Services\VendorService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatorCampaignController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected VendorService $vendorService,
    ) {}

    /**
     * Get the list of active campaigns (vendors) for the authenticated creator's country.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Authorize access using the viewAny method in VendorPolicy
        $this->authorize('viewAny', Vendor::class);

        $creator = $request->user();

        $campaigns = $this->vendorService->getPaginatedVendorsByCountry(
            $creator->country_id,
            $request->input('per_page', 15)
        );

        return $this->paginatedResponse(
            VendorResource::collection($campaigns)
        );
    }
}
