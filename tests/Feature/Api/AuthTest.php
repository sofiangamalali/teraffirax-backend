<?php

namespace Tests\Feature\Api;

use App\Models\Country;
use App\Models\Creator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_register(): void
    {
        $country = Country::factory()->create();

        $data = [
            'name' => 'Test Creator',
            'country_id' => $country->id,
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'gender' => 'male',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'test@example.com');

        $this->assertDatabaseHas('creators', ['email' => 'test@example.com']);
    }

    public function test_creator_can_login_with_email(): void
    {
        $creator = Creator::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['creator', 'token']]);
    }

    public function test_creator_can_login_with_phone(): void
    {
        $creator = Creator::factory()->create([
            'phone' => '0987654321',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'identifier' => '0987654321',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['creator', 'token']]);
    }

    public function test_creator_cannot_login_with_wrong_password(): void
    {
        $creator = Creator::factory()->create([
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identifier']);
    }

    public function test_creator_can_get_profile_when_authenticated(): void
    {
        $creator = Creator::factory()->create();
        $token = $creator->createToken('test')->plainTextToken;

        $response = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $creator->email);
    }

    public function test_creator_cannot_access_profile_without_token(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }
}
