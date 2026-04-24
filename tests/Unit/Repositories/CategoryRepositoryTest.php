<?php

namespace Tests\Unit\Repositories;

use App\Models\Category;
use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CategoryRepository(new Category());
    }

    public function test_can_create_category(): void
    {
        $data = ['name' => 'Electronics'];
        $category = $this->repository->create($data);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Electronics', $category->name);
        $this->assertDatabaseHas('categories', ['name' => 'Electronics']);
    }

    public function test_can_find_category(): void
    {
        $category = Category::factory()->create();

        $found = $this->repository->find($category->id);

        $this->assertNotNull($found);
        $this->assertEquals($category->id, $found->id);
    }

    public function test_can_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $updated = $this->repository->update($category->id, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $result = $this->repository->delete($category->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
