<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
    ) {}

    public function getAllProducts(): Collection
    {
        return $this->productRepository->all();
    }

    public function getPaginatedProducts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage);
    }

    public function getProductById(int $id): Product
    {
        return $this->productRepository->findOrFail($id);
    }

    public function createProduct(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            return $this->productRepository->create($dto->toArray());
        });
    }

    public function updateProduct(int $id, ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($id, $dto) {
            return $this->productRepository->update($id, $dto->toArray());
        });
    }

    public function deleteProduct(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->productRepository->delete($id);
        });
    }
}
