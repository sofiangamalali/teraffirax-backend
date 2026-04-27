<?php

namespace Tests\Feature\Api\Creator;

use App\Models\Category;
use App\Models\Country;
use App\Models\Creator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $country = Country::factory()->create();
        $this->creator = Creator::factory()->create([
            'country_id' => $country->id,
            'is_active' => true,
        ]);

        $this->token = $this->creator->createToken('test_token')->plainTextToken;
    }

    protected function withTokenAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_creator_can_view_profile(): void
    {
        $response = $this->withTokenAuth()
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'country',
                    'categories',
                ],
            ]);
    }

    public function test_creator_can_update_profile_name(): void
    {
        $newName = 'Updated Name';

        $response = $this->withTokenAuth()
            ->putJson('/api/profile', [
                'name' => $newName,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', $newName);

        $this->assertDatabaseHas('creators', [
            'id' => $this->creator->id,
            'name' => $newName,
        ]);
    }

    public function test_creator_can_update_profile_categories(): void
    {
        $categories = Category::factory()->count(2)->create();
        $categoryIds = $categories->pluck('id')->toArray();

        $response = $this->withTokenAuth()
            ->putJson('/api/profile', [
                'categories' => $categoryIds,
            ]);

        $response->assertStatus(200);
        
        $this->assertEquals(2, $this->creator->fresh()->categories()->count());
        $this->assertDatabaseHas('category_creator', [
            'creator_id' => $this->creator->id,
            'category_id' => $categoryIds[0],
        ]);
    }

    public function test_update_profile_validation_errors(): void
    {
        $response = $this->withTokenAuth()
            ->putJson('/api/profile', [
                'categories' => ['invalid'],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['categories.0']);
    }
}
