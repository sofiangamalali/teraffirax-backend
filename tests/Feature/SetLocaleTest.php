<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Country::factory()->create(['id' => 1]);
    }

    public function test_api_returns_arabic_response_with_accept_language_header()
    {
        $categories = Category::factory()->count(2)->create();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'جون دو',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'gender' => 'male',
            'country_id' => 1,
            'categories' => $categories->pluck('id')->toArray(),
        ], ['Accept-Language' => 'ar']);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => true,
            'message' => 'تم الإنشاء بنجاح', // From lang/ar/messages.php
        ]);
    }

    public function test_api_returns_english_response_by_default()
    {
        $categories = Category::factory()->count(2)->create();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'phone' => '1234567891',
            'email' => 'john2@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'gender' => 'male',
            'country_id' => 1,
            'categories' => $categories->pluck('id')->toArray(),
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => true,
            'message' => 'Created successfully', // From lang/en/messages.php
        ]);
    }

    public function test_validation_errors_are_translated_to_arabic()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '', // Required field
        ], ['Accept-Language' => 'ar']);

        $response->assertStatus(422);
        // The error message for 'required' in Arabic
        $response->assertJsonValidationErrors(['name']);
        $this->assertEquals('الحقل name إجباري.', $response->json('errors.name.0'));
    }
}
