<?php

namespace Tests\Feature;

use App\Models\Creator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_creators()
    {
        Creator::factory()->count(3)->create();

        $response = $this->getJson('/api/creators');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'gender', 'balance']
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]);
    }

    public function test_can_create_creator_via_api()
    {
        $data = [
            'name' => 'Jane Doe',
            'phone' => '987654321',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'gender' => 'female'
        ];

        $response = $this->postJson('/api/creators', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Jane Doe')
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->assertDatabaseHas('creators', ['email' => 'jane@example.com']);
    }

    public function test_validation_errors_when_creating_creator()
    {
        $response = $this->postJson('/api/creators', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'gender', 'password']);
    }

    public function test_can_show_creator()
    {
        $creator = Creator::factory()->create();

        $response = $this->getJson('/api/creators/' . $creator->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $creator->id);
    }

    public function test_can_update_creator()
    {
        $creator = Creator::factory()->create();
        $newData = ['name' => 'Updated Name'];

        $response = $this->putJson('/api/creators/' . $creator->id, $newData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('creators', ['id' => $creator->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_creator()
    {
        $creator = Creator::factory()->create();

        $response = $this->deleteJson('/api/creators/' . $creator->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('creators', ['id' => $creator->id]);
    }
}
