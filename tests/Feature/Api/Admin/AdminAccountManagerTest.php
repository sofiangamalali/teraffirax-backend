<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Creator;
use App\Models\Country;
use App\Models\AccountManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccountManagerTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Creator $creator;
    protected string $adminToken;
    protected string $creatorToken;

    protected function setUp(): void
    {
        parent::setUp();

        Country::factory()->create(['id' => 1]);

        $this->admin = Admin::create([
            'id' => 1,
            'name' => 'System Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        $this->adminToken = $this->admin->createToken('admin_token')->plainTextToken;

        $this->creator = Creator::factory()->create([
            'country_id' => 1,
            'available_balance' => 1000.00,
            'is_active' => true,
        ]);
        $this->creatorToken = $this->creator->createToken('creator_token')->plainTextToken;
    }

    protected function withAdminAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->adminToken);
    }

    protected function withCreatorAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->creatorToken);
    }

    public function test_non_admin_cannot_access_account_manager_endpoints(): void
    {
        $response = $this->withCreatorAuth()->getJson('/api/admin/account-managers');
        $response->assertStatus(403);

        $response = $this->withCreatorAuth()->postJson('/api/admin/account-managers', [
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '123456',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_view_paginated_account_managers(): void
    {
        AccountManager::create(['name' => 'Manager A', 'email' => 'a@test.com', 'phone' => '1111']);
        AccountManager::create(['name' => 'Manager B', 'email' => 'b@test.com', 'phone' => '2222']);

        $response = $this->withAdminAuth()->getJson('/api/admin/account-managers?per_page=5');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'created_at', 'updated_at']
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]);

        // There should be exactly 2 we just created
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_admin_can_view_all_account_managers_for_dropdown(): void
    {
        AccountManager::create(['name' => 'Manager A', 'email' => 'a@test.com', 'phone' => '1111']);
        AccountManager::create(['name' => 'Manager B', 'email' => 'b@test.com', 'phone' => '2222']);

        $response = $this->withAdminAuth()->getJson('/api/admin/account-managers/all');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'created_at', 'updated_at']
                ]
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_view_single_account_manager(): void
    {
        $manager = AccountManager::create(['name' => 'Test Manager', 'email' => 'tm@test.com', 'phone' => '123456']);

        $response = $this->withAdminAuth()->getJson("/api/admin/account-managers/{$manager->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Test Manager')
            ->assertJsonPath('data.email', 'tm@test.com');
    }

    public function test_admin_can_create_account_manager(): void
    {
        $response = $this->withAdminAuth()->postJson('/api/admin/account-managers', [
            'name' => 'New Manager',
            'email' => 'new@test.com',
            'phone' => '987654321',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('account_managers', [
            'name' => 'New Manager',
            'email' => 'new@test.com',
        ]);
    }

    public function test_admin_can_update_account_manager(): void
    {
        $manager = AccountManager::create(['name' => 'Old Name', 'email' => 'old@test.com', 'phone' => '1111']);

        $response = $this->withAdminAuth()->putJson("/api/admin/account-managers/{$manager->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
            'phone' => '2222',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('account_managers', [
            'id' => $manager->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ]);
    }

    public function test_admin_cannot_delete_account_manager_if_assigned_to_creator(): void
    {
        $manager = AccountManager::create(['name' => 'Assigned Manager', 'email' => 'assigned@test.com', 'phone' => '9999']);
        
        // Assign this manager to creator
        $this->creator->update(['account_manager_id' => $manager->id]);

        $response = $this->withAdminAuth()->deleteJson("/api/admin/account-managers/{$manager->id}");
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('id')
            ->assertJsonPath('errors.id.0', __('messages.account_manager_assigned_to_creators'));

        $this->assertDatabaseHas('account_managers', ['id' => $manager->id]);
    }

    public function test_admin_can_delete_account_manager_if_not_assigned_to_creator(): void
    {
        $manager = AccountManager::create(['name' => 'Free Manager', 'email' => 'free@test.com', 'phone' => '8888']);

        $response = $this->withAdminAuth()->deleteJson("/api/admin/account-managers/{$manager->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('account_managers', ['id' => $manager->id]);
    }
}
