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
    ) {}

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
}
