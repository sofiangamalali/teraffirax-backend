<?php

namespace Tests\Feature;

use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_vendors()
    {
        Vendor::factory()->count(3)->create();

        $response = $this->getJson('/api/vendors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at']
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]);
    }

    public function test_can_create_vendor_via_api()
    {
        $data = ['name' => 'New Vendor'];

        $response = $this->postJson('/api/vendors', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Vendor');

        $this->assertDatabaseHas('vendors', ['name' => 'New Vendor']);
    }

    public function test_can_show_vendor()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->getJson('/api/vendors/' . $vendor->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $vendor->id);
    }

    public function test_can_update_vendor()
    {
        $vendor = Vendor::factory()->create();
        $newData = ['name' => 'Updated Vendor'];

        $response = $this->putJson('/api/vendors/' . $vendor->id, $newData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Vendor');
    }

    public function test_can_delete_vendor()
    {
        $vendor = Vendor::factory()->create();

        $response = $this->deleteJson('/api/vendors/' . $vendor->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('vendors', ['id' => $vendor->id]);
    }
}
