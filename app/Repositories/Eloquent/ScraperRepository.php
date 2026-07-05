<?php

namespace App\Repositories\Eloquent;

use App\Models\{VendorCategory, Product, ProductBlockedKeyword};
use App\Repositories\Interfaces\ScraperRepositoryInterface;
use App\Enums\VendorCategoryStatusEnum;
use Illuminate\Database\Eloquent\Collection;

class ScraperRepository implements ScraperRepositoryInterface
{
    public function __construct(
        protected VendorCategory $model,
        protected Product $productModel
    ) {}

    public function getVendorCategoriesBySlug(string $slug): Collection
    {
        return $this->model->newQuery()
            ->whereHas('vendor', function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->where('is_active', true);
            })
            ->where('status', VendorCategoryStatusEnum::ACTIVE)
            ->get();
    }

    public function findVendorCategory(int $id): ?VendorCategory
    {
        return $this->model->find($id);
    }

    public function upsertProduct(array $attributes, array $values): Product
    {
        return $this->productModel->updateOrCreate($attributes, $values);
    }

    public function findProduct(int $vendorId, string $externalId): ?Product
    {
        return $this->productModel->newQuery()
            ->where('vendor_id', $vendorId)
            ->where('external_id', $externalId)
            ->first();
    }

    public function getActiveBlockedKeywords(): array
    {
        return ProductBlockedKeyword::where('is_active', true)
            ->pluck('keyword')
            ->toArray();
    }
}
