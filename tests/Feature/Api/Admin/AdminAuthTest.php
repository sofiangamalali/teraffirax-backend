<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);
    }

    /**
     * Test admin can login with valid credentials.
     */
    public function test_admin_can_login_with_correct_credentials(): void
    {
        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'access_token',
                    'refresh_token',
                    'email',
                    'name',
                ]
            ]);

        $this->assertEquals('admin@test.com', $response->json('data.email'));
    }

    /**
     * Test admin login fails with invalid password.
     */
    public function test_admin_cannot_login_with_incorrect_credentials(): void
    {
        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test authenticated admin can logout.
     */
    public function test_admin_can_logout(): void
    {
        $token = $this->admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/auth/logout');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->admin->id,
            'tokenable_type' => Admin::class,
        ]);
    }
}
