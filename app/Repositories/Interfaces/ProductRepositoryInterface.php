<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Product;

    public function findOrFail(int $id): Product;

    public function findByUrl(string $url): ?Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;

    /**
     * Find the best product in a category for a creator based on scoring and publishing restrictions.
     */
    public function findBestProductForCreator(int $creatorId, int $categoryId, array $excludedProductIds, int $socialIntegrationId): ?Product;
}
