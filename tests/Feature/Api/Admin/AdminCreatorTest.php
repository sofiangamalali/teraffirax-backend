<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Creator;
use App\Models\Country;
use App\Models\SocialIntegration;
use App\Models\CreatorVendorTracking;
use App\Models\Vendor;
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
        // Test list creator vendor trackings block
        $response = $this->withCreatorAuth()->getJson("/api/admin/creators/{$this->creator->id}/vendor-trackings");
        $response->assertStatus(403);

        // Test update creator vendor tracking block
        $response = $this->withCreatorAuth()->putJson("/api/admin/creators/{$this->creator->id}/vendor-trackings/1", [
            'track_id' => 'Should fail',
            'is_active' => true,
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

    /**
     * Test admin can manage vendor tracking for a creator.
     */
    public function test_admin_can_manage_creator_vendor_trackings(): void
    {
        $vendor = Vendor::factory()->create(['country_id' => 1, 'is_active' => true]);

        $tracking = CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $vendor->id,
            'track_id' => '',
            'is_active' => false,
        ]);

        // 1. Admin fetches trackings for this creator
        $response = $this->withAdminAuth()->getJson("/api/admin/creators/{$this->creator->id}/vendor-trackings");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($tracking->id, $response->json('data.0.id'));

        // 2. Admin updates the vendor tracking
        $response = $this->withAdminAuth()->putJson("/api/admin/creators/{$this->creator->id}/vendor-trackings/{$tracking->id}", [
            'track_id' => 'my-custom-tag-22',
            'is_active' => true,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('creator_vendor_tracking', [
            'id' => $tracking->id,
            'track_id' => 'my-custom-tag-22',
            'is_active' => true,
        ]);
    }

    /**
     * Test admin can assign an account manager to a creator.
     */
    public function test_admin_can_assign_account_manager_to_creator(): void
    {
        $manager = \App\Models\AccountManager::create([
            'name' => 'John Manager',
            'email' => 'john.mgr@test.com',
            'phone' => '12345678',
        ]);

        $response = $this->withAdminAuth()->patchJson("/api/admin/creators/{$this->creator->id}/assign-account-manager", [
            'account_manager_id' => $manager->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('creators', [
            'id' => $this->creator->id,
            'account_manager_id' => $manager->id,
        ]);
    }

    /**
     * Test admin can unassign an account manager from a creator.
     */
    public function test_admin_can_unassign_account_manager_from_creator(): void
    {
        $manager = \App\Models\AccountManager::create([
            'name' => 'John Manager',
            'email' => 'john.mgr@test.com',
            'phone' => '12345678',
        ]);

        $this->creator->update(['account_manager_id' => $manager->id]);

        $response = $this->withAdminAuth()->patchJson("/api/admin/creators/{$this->creator->id}/assign-account-manager", [
            'account_manager_id' => null,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('creators', [
            'id' => $this->creator->id,
            'account_manager_id' => null,
        ]);
    }

    /**
     * Test validation fails when assigning an invalid account manager ID.
     */
    public function test_assign_account_manager_fails_for_invalid_manager_id(): void
    {
        $response = $this->withAdminAuth()->patchJson("/api/admin/creators/{$this->creator->id}/assign-account-manager", [
            'account_manager_id' => 9999, // Non-existent
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('account_manager_id');
    }

    /**
     * Test non-admin cannot access assign-account-manager endpoint.
     */
    public function test_non_admin_cannot_assign_account_manager(): void
    {
        $response = $this->withCreatorAuth()->patchJson("/api/admin/creators/{$this->creator->id}/assign-account-manager", [
            'account_manager_id' => 1,
        ]);

        $response->assertStatus(403);
    }
}
