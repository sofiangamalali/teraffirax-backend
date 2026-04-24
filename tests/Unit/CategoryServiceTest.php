<?php

namespace Tests\Unit;

use App\DTOs\CategoryDTO;
use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Mockery;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    protected $repository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(CategoryRepositoryInterface::class);
        $this->service = new CategoryService($this->repository);
    }

    public function test_can_create_category()
    {
        $data = ['name' => 'Electronics'];
        $dto = new CategoryDTO(...$data);
        $categoryModel = new Category($data);
        
        $this->repository->shouldReceive('create')
            ->once()
            ->andReturn($categoryModel);

        $result = $this->service->createCategory($dto);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals('Electronics', $result->name);
    }

    public function test_can_get_category_by_id()
    {
        $category = new Category(['name' => 'Fashion']);
        $category->id = 1;
        
        $this->repository->shouldReceive('findOrFail')
            ->once()
            ->with(1)
            ->andReturn($category);

        $result = $this->service->getCategoryById(1);

        $this->assertEquals(1, $result->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
