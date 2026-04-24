<?php

namespace Tests\Feature\Api;

use App\Models\Country;
use App\Models\Creator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Creator::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_countries(): void
    {
        Country::factory()->count(3)->create();

        $response = $this->getJson('/api/countries');

        $response->assertStatus(200)
            ->assertJsonStructure([
            'status',
            'message',
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total']
        ]);
    }

    public function test_can_create_country(): void
    {
        $data = [
            'name' => 'Egypt',
            'iso2' => 'EG',
            'phone_code' => '+20',
            'phone_min_length' => 11,
            'phone_max_length' => 11,
        ];

        $response = $this->postJson('/api/countries', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Egypt']);

        $this->assertDatabaseHas('countries', ['iso2' => 'EG']);
    }

    public function test_can_show_country(): void
    {
        $country = Country::factory()->create();

        $response = $this->getJson("/api/countries/{$country->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $country->id]);
    }

    public function test_can_update_country(): void
    {
        $country = Country::factory()->create();
        $data = ['name' => 'Updated Name'];

        $response = $this->putJson("/api/countries/{$country->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_can_delete_country(): void
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson("/api/countries/{$country->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}
