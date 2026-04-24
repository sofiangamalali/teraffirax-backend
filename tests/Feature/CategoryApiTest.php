<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ],
                'meta' => ['current_page', 'total']
            ]);
    }

    public function test_can_create_category_via_api()
    {
        $data = [
            'name' => 'Home & Kitchen',
        ];

        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Home & Kitchen');

        $this->assertDatabaseHas('categories', ['name' => 'Home & Kitchen']);
    }

    public function test_validation_errors_when_creating_category()
    {
        $response = $this->postJson('/api/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_show_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $category->id);
    }

    public function test_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        $newData = ['name' => 'New Awesome Name'];

        $response = $this->putJson('/api/categories/' . $category->id, $newData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Awesome Name');
        
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Awesome Name']);
    }

    public function test_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson('/api/categories/' . $category->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
