<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        protected Product $model,
    ) {
    }

    public function all(): Collection
    {
        return $this->model->with('vendor')->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('vendor')->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        return $this->model->with('vendor')->find($id);
    }

    public function findOrFail(int $id): Product
    {
        return $this->model->with('vendor')->findOrFail($id);
    }

    public function findByUrl(string $url): ?Product
    {
        return $this->model->with('vendor')->where('product_url', $url)->first();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);
        $product->update($data);

        return $product->fresh();
    }

    public function delete(int $id): bool
    {
        $product = $this->findOrFail($id);

        return $product->delete();
    }

    /**
     * Find the best product in a category for a creator based on scoring and publishing restrictions.
     */
    public function findBestProductForCreator(int $creatorId, int $categoryId, array $excludedProductIds, int $socialIntegrationId): ?Product
    {
        $sub24 = now()->subHours(24);
        $sub3 = now()->subHours(3);

        return $this->model->newQuery()
            ->join('creator_vendor_tracking as cva', function ($join) use ($creatorId) {
                $join->on('cva.vendor_id', '=', 'products.vendor_id')
                    ->where('cva.creator_id', '=', $creatorId)
                    ->where('cva.is_active', '=', true);
            })
            ->join('vendors as v', function ($join) {
                $join->on('v.id', '=', 'products.vendor_id')
                    ->where('v.is_active', '=', true);
            })
            ->where('products.category_id', $categoryId)
            ->where('products.status', 'active')
            ->whereNotIn('products.id', $excludedProductIds)
            ->whereNotIn('products.id', function ($query) use ($socialIntegrationId, $sub24) {
                $query->select('product_id')
                    ->from('telegram_product_posts')
                    ->where('social_integration_id', $socialIntegrationId)
                    ->where('post_status', 'posted')
                    ->where('posted_at', '>', $sub24);
            })
            ->whereNotIn('products.id', function ($query) use ($sub3) {
                $query->select('product_id')
                    ->from('telegram_product_posts')
                    ->where('post_status', 'posted')
                    ->where('posted_at', '>', $sub3);
            })
            ->orderBy('products.score', 'desc')
            ->select('products.*')
            ->first();
    }
}
