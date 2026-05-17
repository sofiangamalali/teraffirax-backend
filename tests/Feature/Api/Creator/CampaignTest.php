<?php

namespace Tests\Feature\Api\Creator;

use App\Models\Country;
use App\Models\Creator;
use App\Models\Vendor;
use App\Models\CreatorVendorTrackingId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;
    protected Country $country;
    protected Creator $creator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::factory()->create([
            'currency' => ['en' => 'EGP', 'ar' => 'ج.م'],
        ]);

        $this->creator = Creator::factory()->create([
            'country_id' => $this->country->id,
            'is_active' => true,
        ]);

        $this->token = $this->creator->createToken('test_token')->plainTextToken;
    }

    protected function withTokenAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_creator_can_view_all_campaigns_active_and_inactive_for_their_country(): void
    {
        // 1. Create an active vendor matching the creator's country
        Vendor::create([
            'name' => 'Active Jumia',
            'country_id' => $this->country->id,
            'domain' => 'https://jumia.com',
            'logo' => 'jumia.png',
            'is_active' => true,
        ]);

        // 2. Create an inactive vendor in the creator's country
        Vendor::create([
            'name' => 'Inactive Noon',
            'country_id' => $this->country->id,
            'domain' => 'https://noon.com',
            'logo' => 'noon.png',
            'is_active' => false,
        ]);

        // 3. Create an active vendor in another country
        $otherCountry = Country::factory()->create([
            'currency' => ['en' => 'SAR', 'ar' => 'ر.س'],
        ]);
        Vendor::create([
            'name' => 'Other Country Amazon',
            'country_id' => $otherCountry->id,
            'domain' => 'https://amazon.sa',
            'logo' => 'amazon.png',
            'is_active' => true,
        ]);

        // Request the campaigns endpoint
        $response = $this->withTokenAuth()
            ->getJson('/api/campaigns');

        // Assert response structure and content according to ApiResponseTrait pagination format
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'logo',
                        'is_active',
                        'country_id',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        // Assert it returns BOTH the active and inactive vendors of the creator's country (total 2)
        $data = $response->json('data');
        $this->assertCount(2, $data);

        // Verify active and inactive values
        $names = collect($data)->pluck('name')->toArray();
        $this->assertContains('Active Jumia', $names);
        $this->assertContains('Inactive Noon', $names);
    }

    public function test_cannot_create_tracking_link_for_inactive_vendor(): void
    {
        // 1. Create an inactive vendor
        $vendor = Vendor::create([
            'name' => 'Inactive Noon',
            'country_id' => $this->country->id,
            'domain' => 'https://noon.com',
            'logo' => 'noon.png',
            'is_active' => false,
        ]);

        // 2. Add subscription tracking ID for creator
        CreatorVendorTrackingId::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $vendor->id,
            'tracking_id' => 'noon123',
        ]);

        // Request creation of a tracking link for the inactive vendor
        $response = $this->withTokenAuth()
            ->postJson('/api/tracking-links', [
                'redirect_url' => 'https://noon.com/product/123',
            ]);

        // Assert that it fails with 422 Vendor Inactive error
        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'This vendor is currently inactive and cannot be used.',
            ]);
    }
}
