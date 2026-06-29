<?php

namespace App\Services;

use App\DTOs\ScraperBulkUpsertDTO;
use App\Repositories\Interfaces\ScraperRepositoryInterface;
use App\Services\ScoreCalculator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ScraperService
{
    public function __construct(
        protected ScraperRepositoryInterface $scraperRepository
    ) {}

    /**
     * Get active vendor categories by vendor slug.
     */
    public function getVendorCategoriesBySlug(string $slug): Collection
    {
        return $this->scraperRepository->getVendorCategoriesBySlug($slug);
    }

    /**
     * Bulk upsert products and calculate score and commission.
     */
    public function upsertProducts(ScraperBulkUpsertDTO $dto): Collection
    {
        return DB::transaction(function () use ($dto) {
            $vendorCategory = $this->scraperRepository->findVendorCategory($dto->vendor_category_id);
            $commissionPercent = $vendorCategory ? (float) ($vendorCategory->commission ?? 0) : 0;

            $upsertedProducts = collect();

            foreach ($dto->products as $productDto) {
                $priceAfter = (float) $productDto->price_after;
                $discountPercentage = $productDto->discount_percentage !== null ? (float) $productDto->discount_percentage : 0.0;
                $commissionValue = $priceAfter * ($commissionPercent / 100);
                $rank = $productDto->rank_in_vendor_category !== null ? (int) $productDto->rank_in_vendor_category : 1;

                // Calculate product score
                $scoreFloat = ScoreCalculator::calculate(
                    $priceAfter,
                    $discountPercentage,
                    $commissionPercent,
                    $rank
                );
                $score = (int) round($scoreFloat * 1000);

                $attributes = [
                    'vendor_id' => $dto->vendor_id,
                    'external_id' => $productDto->external_id,
                ];

                $values = [
                    'category_id' => $dto->category_id,
                    'vendor_category_id' => $dto->vendor_category_id,
                    'name' => $productDto->name,
                    'url' => $productDto->url,
                    'price_before' => $productDto->price_before,
                    'price_after' => $priceAfter,
                    'discount_percentage' => $discountPercentage,
                    'rank_in_vendor_category' => $productDto->rank_in_vendor_category,
                    'commission' => $commissionValue,
                    'score' => $score,
                    'last_validated_at' => now(),
                ];

                $product = $this->scraperRepository->upsertProduct($attributes, $values);
                $upsertedProducts->push($product);
            }

            return $upsertedProducts;
        });
    }
}
