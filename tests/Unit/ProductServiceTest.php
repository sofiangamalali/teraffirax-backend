<?php

namespace Tests\Unit;

use App\DTOs\ProductDTO;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\ProductService;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    protected $repository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ProductRepositoryInterface::class);
        $this->service = new ProductService($this->repository);
    }

    public function test_can_create_product()
    {
        $data = [
            'vendor_id'   => 1,
            'name'        => 'Test Product',
            'price_after' => 100.00,
            'product_url' => 'https://example.com'
        ];
        $dto = new ProductDTO(...$data);
        $productModel = new Product($data);
        
        $this->repository->shouldReceive('create')
            ->once()
            ->andReturn($productModel);

        $result = $this->service->createProduct($dto);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('Test Product', $result->name);
    }

    public function test_can_get_product_by_id()
    {
        $product = new Product(['name' => 'Test Product']);
        $product->id = 1;
        
        $this->repository->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($product);

        $result = $this->service->getProductById(1);

        $this->assertEquals(1, $result->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
