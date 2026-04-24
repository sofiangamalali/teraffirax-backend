<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ProductService $productService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        $products = $this->productService->getPaginatedProducts($request->query('per_page', 15));
        return $this->paginatedResponse(ProductResource::collection($products), 'messages.fetched');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $dto = ProductDTO::fromRequest($request);
        $product = $this->productService->createProduct($dto);
        return $this->successResponse(new ProductResource($product), 'messages.created', 201);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);
        $product->load('vendor');
        return $this->successResponse(new ProductResource($product), 'messages.fetched');
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        $dto = ProductDTO::fromRequest($request);
        $updatedProduct = $this->productService->updateProduct($product->id, $dto);
        return $this->successResponse(new ProductResource($updatedProduct), 'messages.updated');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);
        $this->productService->deleteProduct($product->id);
        return $this->successResponse(null, 'messages.deleted');
    }
}
