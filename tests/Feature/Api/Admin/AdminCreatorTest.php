<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Creator;
use App\Models\Country;
use App\Models\SocialIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCreatorTest extends TestCase
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
            'name' => 'System Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);
        $this->adminToken = $this->admin->createToken('admin_token')->plainTextToken;

        $this->creator = Creator::factory()->create([
            'country_id' => 1,
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

    /**
     * Test creator users cannot access admin creator endpoints.
     */
    public function test_non_admin_cannot_access_admin_creator_endpoints(): void
    {
        // Test list creators block
        $response = $this->withCreatorAuth()->getJson('/api/admin/creators');
        $response->assertStatus(403);

        // Test toggle status block
        $response = $this->withCreatorAuth()->patchJson("/api/admin/creators/{$this->creator->id}/toggle-active");
        $response->assertStatus(403);

        // Test list creator social integrations block
        $response = $this->withCreatorAuth()->getJson("/api/admin/creators/{$this->creator->id}/social-integrations");
        $response->assertStatus(403);

        // Test update creator social integration block
        $response = $this->withCreatorAuth()->putJson("/api/admin/creators/{$this->creator->id}/social-integrations/1", [
            'name' => 'Should fail',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test admin users cannot access creator endpoints.
     */
    public function test_admin_cannot_access_creator_endpoints(): void
    {
        $response = $this->withAdminAuth()->getJson('/api/profile');
        $response->assertStatus(403);
    }

    /**
     * Test admin can fetch paginated list of creators.
     */
    public function test_admin_can_view_paginated_creators(): void
    {
        Creator::factory()->count(10)->create(['country_id' => 1]);

        $response = $this->withAdminAuth()->getJson('/api/admin/creators?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'is_active',
                        'is_email_verified',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(11, $response->json('meta.total')); // 1 from setup + 10 created
    }

    /**
     * Test admin can toggle active status of creator.
     */
    public function test_admin_can_toggle_creator_active(): void
    {
        $this->assertTrue($this->creator->is_active);

        $response = $this->withAdminAuth()->patchJson("/api/admin/creators/{$this->creator->id}/toggle-active");

        $response->assertStatus(200);
        $this->creator->refresh();
        $this->assertFalse($this->creator->is_active);

        // Toggle back to active
        $response = $this->withAdminAuth()->patchJson("/api/admin/creators/{$this->creator->id}/toggle-active");
        $response->assertStatus(200);
        $this->creator->refresh();
        $this->assertTrue($this->creator->is_active);
    }

    /**
     * Test admin can manage social integrations for a creator.
     */
    public function test_admin_can_manage_creator_social_integrations(): void
    {
        // 1. Create a social integration for the creator
        $integration = SocialIntegration::create([
            'creator_id' => $this->creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'active',
            'name' => 'Original Channel',
            'username' => 'orig_channel',
            'external_id' => '123',
        ]);

        // 2. Admin fetches integrations for this creator
        $response = $this->withAdminAuth()->getJson("/api/admin/creators/{$this->creator->id}/social-integrations");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // 3. Admin updates the social integration
        $response = $this->withAdminAuth()->putJson("/api/admin/creators/{$this->creator->id}/social-integrations/{$integration->id}", [
            'name' => 'Updated Tiktok Profile',
            'status' => 'inactive',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('social_integrations', [
            'id' => $integration->id,
            'name' => 'Updated Tiktok Profile',
            'status' => 'inactive',
        ]);
    }
}
