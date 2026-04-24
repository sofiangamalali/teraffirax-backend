<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CategoryDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CategoryService $categoryService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);
        $perPage = $request->query('per_page', 15);
        $categories = $this->categoryService->getPaginatedCategories((int) $perPage);

        return $this->paginatedResponse(
            CategoryResource::collection($categories),
            'messages.fetched'
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        $dto = CategoryDTO::fromRequest($request);
        $category = $this->categoryService->createCategory($dto);

        return $this->successResponse(
            new CategoryResource($category),
            'messages.created',
            201
        );
    }

    public function show(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        return $this->successResponse(
            new CategoryResource($category),
            'messages.fetched'
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);
        $dto = CategoryDTO::fromRequest($request);
        $updatedCategory = $this->categoryService->updateCategory($category->id, $dto);

        return $this->successResponse(
            new CategoryResource($updatedCategory),
            'messages.updated'
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);
        $this->categoryService->deleteCategory($category->id);

        return $this->successResponse(null, 'messages.deleted');
    }
}
