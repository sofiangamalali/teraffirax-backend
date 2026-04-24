<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'price_after', 'vendor']
                ],
                'meta' => ['current_page', 'total']
            ]);
    }

    public function test_can_create_product_via_api()
    {
        $vendor = Vendor::factory()->create();
        $data = [
            'vendor_id'    => $vendor->id,
            'name'         => 'Awesome Product',
            'price_after'  => 80.50,
            'product_url'  => 'https://cashy-tok.com/item'
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Awesome Product')
            ->assertJsonPath('data.price_before', null);

        $this->assertDatabaseHas('products', ['name' => 'Awesome Product']);
    }

    public function test_validation_errors_when_creating_product()
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_id', 'name', 'price_after', 'product_url']);
    }

    public function test_can_show_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonStructure(['data' => ['vendor']]);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();
        $newData = ['name' => 'Updated Product Name'];

        $response = $this->putJson('/api/products/' . $product->id, $newData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Product Name');
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
