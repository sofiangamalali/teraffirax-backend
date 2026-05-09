<?php

namespace Tests\Feature\Api\Creator;

use App\Models\Commission;
use App\Models\Creator;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $creator;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'iso2' => 'eg',
            'phone_code' => '+20',
            'phone_min_length' => 11,
            'phone_max_length' => 11,
        ]);

        $this->creator = Creator::factory()->create([
            'is_active' => true,
            'country_id' => $country->id
        ]);

        $this->token = $this->creator->createToken('test_token')->plainTextToken;
    }

    protected function withAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_can_list_commissions()
    {
        Commission::factory()->count(5)->create([
            'creator_id' => $this->creator->id,
        ]);

        $response = $this->withAuth()
            ->getJson('/api/commissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'creator_id',
                        'vendor_id',
                        'date',
                        'clicks_count',
                        'items_count',
                        'returned_count',
                        'revenue_amount',
                        'commission_amount',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);
    }

    public function test_can_filter_commissions_by_date()
    {
        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => '2026-05-01',
        ]);

        Commission::factory()->create([
            'creator_id' => $this->creator->id,
            'date' => '2026-05-10',
        ]);

        $response = $this->withAuth()
            ->getJson('/api/commissions?start_date=2026-05-05');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('2026-05-10', $response->json('data.0.date'));
    }
}
