<?php

namespace App\Services;

use App\DTOs\CategoryDTO;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->all();
    }

    public function getPaginatedCategories(int $perPage = 15): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($perPage);
    }

    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->all();
    }

    public function getCategoryById(int $id): Category
    {
        return $this->categoryRepository->findOrFail($id);
    }

    public function createCategory(CategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($dto) {
            return $this->categoryRepository->create($dto->toArray());
        });
    }

    public function updateCategory(int $id, CategoryDTO $dto): Category
    {
        return DB::transaction(function () use ($id, $dto) {
            return $this->categoryRepository->update($id, $dto->toArray());
        });
    }

    public function deleteCategory(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->categoryRepository->delete($id);
        });
    }
}
