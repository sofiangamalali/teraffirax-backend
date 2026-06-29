<?php

namespace App\Http\Controllers\Api\Scraper;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scraper\BulkUpsertProductsRequest;
use App\DTOs\ScraperBulkUpsertDTO;
use App\Http\Resources\{ProductResource, VendorCategoryResource};
use App\Services\ScraperService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ScraperController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ScraperService $scraperService
    ) {
    }

    /**
     * Health check endpoint for the scraper.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Get active vendor categories by vendor slug.
     */
    public function vendorCategories(string $slug): JsonResponse
    {
        $categories = $this->scraperService->getVendorCategoriesBySlug($slug);

        return $this->successResponse(
            VendorCategoryResource::collection($categories),
            'messages.fetched'
        );
    }

    /**
     * Bulk upsert products scraped from a vendor category.
     */
    public function upsertProducts(BulkUpsertProductsRequest $request): JsonResponse
    {
        $dto = ScraperBulkUpsertDTO::fromRequest($request);
        $products = $this->scraperService->upsertProducts($dto);

        return $this->successResponse(
            ProductResource::collection($products),
            'messages.created',
            201
        );
    }
}
