<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Creator;
use App\Models\Country;
use App\Models\Vendor;
use App\Models\CreatorVendorTracking;
use App\Models\Commission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommissionManualEntryTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Creator $creator;
    protected Vendor $vendor;
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

        $this->vendor = Vendor::create([
            'name' => 'Amazon',
            'slug' => 'amazon',
            'domain' => 'amazon.com',
            'is_active' => true,
            'country_id' => 1,
        ]);
    }

    protected function withAdminAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->adminToken);
    }

    protected function withCreatorAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->creatorToken);
    }

    public function test_non_admin_cannot_access_commission_manual_entry_endpoints(): void
    {
        $response = $this->withCreatorAuth()->getJson('/api/admin/vendors/all');
        $response->assertStatus(403);

        $response = $this->withCreatorAuth()->postJson('/api/admin/commissions', [
            'track_id' => 'amzn-01',
            'vendor_id' => $this->vendor->id,
            'earned_at' => now()->toDateString(),
            'revenue_amount' => 1000.00,
            'total_commission' => 100.00,
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_view_active_vendors_only(): void
    {
        $activeVendor = $this->vendor;

        $inactiveVendor = Vendor::create([
            'name' => 'Noon',
            'slug' => 'noon',
            'domain' => 'noon.com',
            'is_active' => false,
            'country_id' => 1,
        ]);

        $response = $this->withAdminAuth()->getJson('/api/admin/vendors/all');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($activeVendor->id, $data[0]['id']);
    }

    public function test_admin_can_create_commission_manual_entry_with_default_platform_percentage(): void
    {
        $tracking = CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'track_id' => 'amzn-tracking-01',
            'is_active' => true,
            // default platform_commission_percentage is 50.00%
        ]);

        $today = now()->toDateString();

        $response = $this->withAdminAuth()->postJson('/api/admin/commissions', [
            'track_id' => 'amzn-tracking-01',
            'vendor_id' => $this->vendor->id,
            'earned_at' => $today,
            'revenue_amount' => 1000.00,
            'total_commission' => 100.00, // full raw commission
            'clicks_count' => 50,
            'items_count' => 10,
            'returned_count' => 2,
        ]);

        $response->assertStatus(201);

        // Verification: Commission record should be stored in db
        $this->assertDatabaseHas('commissions', [
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'earned_at' => $today . ' 00:00:00',
            'commission_amount' => 50.00, // 100 * (100 - 50)/100 = 50.00
            'revenue_amount' => 1000.00,
            'returned_count' => 2,
            'calculated_at' => null,
            'matured_at' => null,
        ]);
    }

    public function test_admin_can_create_commission_manual_entry_with_custom_platform_percentage(): void
    {
        $tracking = CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'track_id' => 'amzn-tracking-02',
            'is_active' => true,
            'platform_commission_percentage' => 30.00 // platform takes 30%, creator gets 70%
        ]);

        $today = now()->toDateString();

        $response = $this->withAdminAuth()->postJson('/api/admin/commissions', [
            'track_id' => 'amzn-tracking-02',
            'vendor_id' => $this->vendor->id,
            'earned_at' => $today,
            'revenue_amount' => 1000.00,
            'total_commission' => 200.00,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('commissions', [
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'commission_amount' => 140.00, // 200 * 70% = 140.00
            'revenue_amount' => 1000.00,
        ]);
    }

    public function test_admin_cannot_create_duplicate_commission_on_same_day(): void
    {
        $tracking = CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'track_id' => 'amzn-tracking-03',
            'is_active' => true,
        ]);

        $today = now()->toDateString();

        // 1. Create first commission
        $response = $this->withAdminAuth()->postJson('/api/admin/commissions', [
            'track_id' => 'amzn-tracking-03',
            'vendor_id' => $this->vendor->id,
            'earned_at' => $today,
            'revenue_amount' => 1000.00,
            'total_commission' => 100.00,
        ]);
        $response->assertStatus(201);

        // 2. Try duplicate commission for the same tracking code (same vendor/creator) on the same date
        $response2 = $this->withAdminAuth()->postJson('/api/admin/commissions', [
            'track_id' => 'amzn-tracking-03',
            'vendor_id' => $this->vendor->id,
            'earned_at' => $today,
            'revenue_amount' => 500.00,
            'total_commission' => 50.00,
        ]);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors('earned_at')
            ->assertJsonPath('errors.earned_at.0', 'A commission has already been added for this vendor and creator on this date.');
    }

    public function test_admin_can_give_special_offer_0_percent_commission_by_setting_100_percent_platform_cut(): void
    {
        $tracking = CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'track_id' => 'amzn-tracking-04',
            'is_active' => true,
            'platform_commission_percentage' => 100.00 // platform takes all, creator gets 0%
        ]);

        $today = now()->toDateString();

        $response = $this->withAdminAuth()->postJson('/api/admin/commissions', [
            'track_id' => 'amzn-tracking-04',
            'vendor_id' => $this->vendor->id,
            'earned_at' => $today,
            'revenue_amount' => 1000.00,
            'total_commission' => 100.00,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('commissions', [
            'creator_id' => $this->creator->id,
            'vendor_id' => $this->vendor->id,
            'commission_amount' => 0.00, // 100 * (100 - 100)% = 0.00
        ]);
    }

    public function test_admin_cannot_create_commission_if_tracking_code_does_not_match_selected_vendor(): void
    {
        $otherVendor = Vendor::create([
            'name' => 'Noon',
            'slug' => 'noon',
            'domain' => 'noon.com',
            'is_active' => true,
            'country_id' => 1,
        ]);

        // tracking for Noon
        $tracking = CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $otherVendor->id,
            'track_id' => 'noon-tracking-01',
            'is_active' => true,
        ]);

        $today = now()->toDateString();

        // Try to post with tracking 'noon-tracking-01' but vendor as 'Amazon'
        $response = $this->withAdminAuth()->postJson('/api/admin/commissions', [
            'track_id' => 'noon-tracking-01',
            'vendor_id' => $this->vendor->id, // Amazon (doesn't match tracking's vendor Noon)
            'earned_at' => $today,
            'revenue_amount' => 1000.00,
            'total_commission' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('track_id')
            ->assertJsonPath('errors.track_id.0', 'The tracking code does not match the selected vendor.');
    }
}
