<?php

namespace Tests\Feature\Api;

use App\Models\Country;
use App\Models\Creator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorPhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin or just a user if auth is not strictly enforced for creators store
        $this->actingAs(Creator::factory()->create());
    }

    public function test_fails_when_phone_is_too_short(): void
    {
        $country = Country::factory()->create([
            'phone_min_length' => 10,
            'phone_max_length' => 12,
        ]);

        $data = [
            'name' => 'John Doe',
            'country_id' => $country->id,
            'phone' => '123456789', // 9 digits
            'email' => 'john@example.com',
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/creators', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_fails_when_phone_is_too_long(): void
    {
        $country = Country::factory()->create([
            'phone_min_length' => 10,
            'phone_max_length' => 12,
        ]);

        $data = [
            'name' => 'John Doe',
            'country_id' => $country->id,
            'phone' => '1234567890123', // 13 digits
            'email' => 'john@example.com',
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/creators', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_passes_when_phone_is_exactly_right(): void
    {
        $country = Country::factory()->create([
            'phone_min_length' => 10,
            'phone_max_length' => 10,
        ]);

        $data = [
            'name' => 'John Doe',
            'country_id' => $country->id,
            'phone' => '1234567890', // 10 digits
            'email' => 'john@example.com',
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/creators', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('creators', [
            'email' => 'john@example.com',
            'country_id' => $country->id
        ]);
    }
}
