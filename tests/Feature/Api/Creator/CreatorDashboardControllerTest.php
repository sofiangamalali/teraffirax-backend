<?php

namespace Tests\Feature\Api\Creator;

use App\Models\Commission;
use App\Models\Creator;
use App\Models\Country;
use App\Models\CreatorDashboardVideo;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $creator;
    protected string $token;
    protected Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'iso2' => 'eg',
            'phone_code' => '+20',
            'phone_min_length' => 11,
            'phone_max_length' => 11,
            'currency' => 'EGP',
        ]);

        $this->creator = Creator::factory()->create([
            'is_active' => true,
            'country_id' => $this->country->id,
            'pending_balance' => 150.00,
            'available_balance' => 2000.00,
            'withdrawn_balance' => 500.00,
        ]);

        $this->token = $this->creator->createToken('test_token')->plainTextToken;
    }

    protected function withAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_creator_can_view_wallet_balances(): void
    {
        $response = $this->withAuth()
            ->getJson('/api/dashboard/wallet');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'pending_balance',
                    'available_balance',
                    'withdrawn_balance',
                    'currency',
                ]
            ]);

        $response->assertJsonPath('data.pending_balance', '150.00');
        $response->assertJsonPath('data.available_balance', '2000.00');
        $response->assertJsonPath('data.withdrawn_balance', '500.00');
        $response->assertJsonPath('data.currency', 'EGP');
    }

    public function test_creator_can_view_graph_with_defaults(): void
    {
        // Create sample commissions for the creator
        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => now()->format('Y-m-d'),
            'clicks_count' => 100,
            'items_count' => 10,
            'returned_count' => 1,
            'commission_amount' => 50.00,
        ]);

        $response = $this->withAuth()
            ->getJson('/api/dashboard/graph');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'graph' => [
                        '*' => [
                            'date',
                            'commission',
                            'clicks',
                            'orders',
                            'currency',
                        ]
                    ],
                    'summary' => [
                        'clicks',
                        'orders',
                        'conversion_rate',
                        'shipped_items',
                        'returned_items',
                        'commission',
                        'currency',
                    ]
                ]
            ]);

        // Assert response values
        $response->assertJsonPath('data.summary.clicks', 100);
        $response->assertJsonPath('data.summary.orders', 10);
        $response->assertJsonPath('data.summary.conversion_rate', 10);
        $response->assertJsonPath('data.summary.shipped_items', 10);
        $response->assertJsonPath('data.summary.returned_items', 1);
        $response->assertJsonPath('data.summary.commission', 50);
        $response->assertJsonPath('data.summary.currency', 'EGP');
    }

    public function test_creator_can_filter_graph_by_days(): void
    {
        // Create commission outside the 30 days window
        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => now()->subDays(40)->format('Y-m-d'),
            'clicks_count' => 50,
            'items_count' => 5,
            'commission_amount' => 20.00,
        ]);

        // Create commission inside the 30 days window
        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => now()->subDays(10)->format('Y-m-d'),
            'clicks_count' => 100,
            'items_count' => 10,
            'commission_amount' => 50.00,
        ]);

        // Filter last 30 days
        $response = $this->withAuth()
            ->getJson('/api/dashboard/graph?days=30');

        $response->assertStatus(200);
        $response->assertJsonPath('data.summary.clicks', 100);
        $response->assertJsonPath('data.summary.commission', 50);
    }

    public function test_creator_can_filter_graph_by_custom_dates(): void
    {
        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => '2026-05-01',
            'clicks_count' => 50,
            'commission_amount' => 20.00,
        ]);

        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => '2026-05-15',
            'clicks_count' => 100,
            'commission_amount' => 50.00,
        ]);

        $response = $this->withAuth()
            ->getJson('/api/dashboard/graph?date_from=2026-05-05&date_to=2026-05-20');

        $response->assertStatus(200);
        $response->assertJsonPath('data.summary.clicks', 100);
        $response->assertJsonPath('data.summary.commission', 50);
    }

    public function test_graph_exceeds_three_months_groups_by_month(): void
    {
        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => '2026-01-15',
            'clicks_count' => 10,
            'commission_amount' => 5.00,
        ]);

        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => '2026-01-20',
            'clicks_count' => 20,
            'commission_amount' => 10.00,
        ]);

        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => '2026-02-10',
            'clicks_count' => 30,
            'commission_amount' => 15.00,
        ]);

        // Filter spanning 4 months (120 days)
        $response = $this->withAuth()
            ->getJson('/api/dashboard/graph?date_from=2026-01-01&date_to=2026-05-01');

        $response->assertStatus(200);
        
        $graph = $response->json('data.graph');
        $this->assertCount(2, $graph);
        $this->assertEquals('2026-01', $graph[0]['date']);
        $this->assertEquals(30, $graph[0]['clicks']); // 10 + 20
        $this->assertEquals(15.0, $graph[0]['commission']); // 5 + 10

        $this->assertEquals('2026-02', $graph[1]['date']);
        $this->assertEquals(30, $graph[1]['clicks']);
        $this->assertEquals(15.0, $graph[1]['commission']);
    }

    public function test_graph_filters_validation_errors(): void
    {
        // Invalid days parameter
        $response = $this->withAuth()
            ->getJson('/api/dashboard/graph?days=50');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days']);

        // Invalid date_to is before date_from
        $response = $this->withAuth()
            ->getJson('/api/dashboard/graph?date_from=2026-05-10&date_to=2026-05-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date_to']);
    }

    public function test_creator_can_view_paginated_dashboard_videos(): void
    {
        // Create 25 dashboard videos for this creator's country
        CreatorDashboardVideo::factory()->count(25)->create([
            'country_id' => $this->country->id,
        ]);

        // Create 5 dashboard videos for another country (should be filtered out)
        $anotherCountry = Country::create([
            'name' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية'],
            'iso2' => 'sa',
            'phone_code' => '+966',
            'phone_min_length' => 9,
            'phone_max_length' => 9,
            'currency' => 'SAR',
        ]);
        CreatorDashboardVideo::factory()->count(5)->create([
            'country_id' => $anotherCountry->id,
        ]);

        // Request first page with per_page = 10
        $response = $this->withAuth()
            ->getJson('/api/dashboard/videos?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'video_url',
                        'thumbnail_url',
                        'country' => [
                            'id',
                            'name',
                            'currency',
                        ],
                        'views_count',
                        'likes_count',
                        'comments_count',
                        'shares_count',
                        'total_orders',
                        'total_commission',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);

        // Total should only count the 25 videos from the creator's country
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.last_page', 3);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', 25);

        // Request second page
        $responsePage2 = $this->withAuth()
            ->getJson('/api/dashboard/videos?per_page=10&page=2');

        $responsePage2->assertStatus(200);
        $responsePage2->assertJsonCount(10, 'data');
        $responsePage2->assertJsonPath('meta.current_page', 2);
    }

    public function test_creator_can_view_dashboard_filters(): void
    {
        // 1. Create an active vendor in the creator's country
        $activeVendor = Vendor::create([
            'country_id' => $this->creator->country_id,
            'is_active' => true,
            'name' => 'Active Egypt Vendor',
            'domain' => 'https://www.active-egypt.com',
            'logo' => 'active-egypt.png',
        ]);

        // 2. Create an inactive vendor in the creator's country
        Vendor::create([
            'country_id' => $this->creator->country_id,
            'is_active' => false,
            'name' => 'Inactive Egypt Vendor',
            'domain' => 'https://www.inactive-egypt.com',
            'logo' => 'inactive-egypt.png',
        ]);

        // 3. Create an active vendor in another country
        $anotherCountry = Country::create([
            'name' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية'],
            'iso2' => 'sa',
            'phone_code' => '+966',
            'phone_min_length' => 9,
            'phone_max_length' => 9,
            'currency' => 'SAR',
        ]);
        Vendor::create([
            'country_id' => $anotherCountry->id,
            'is_active' => true,
            'name' => 'Active Saudi Vendor',
            'domain' => 'https://www.active-saudi.com',
            'logo' => 'active-saudi.png',
        ]);

        $response = $this->withAuth()
            ->getJson('/api/dashboard/filters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'days',
                    'vendors' => [
                        '*' => [
                            'id',
                            'name',
                            'logo',
                        ]
                    ]
                ]
            ]);

        // Assert days presets
        $response->assertJsonPath('data.days', [30, 60, 90]);

        // Assert vendors contains exactly the 1 active vendor from the creator's country
        $vendors = $response->json('data.vendors');
        $this->assertCount(1, $vendors);
        $this->assertEquals($activeVendor->id, $vendors[0]['id']);
        $this->assertEquals('Active Egypt Vendor', $vendors[0]['name']);
    }
}
