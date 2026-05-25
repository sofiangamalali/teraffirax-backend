<?php

namespace Tests\Feature\Api\Creator;

use App\Models\Creator;
use App\Models\Country;
use App\Models\CreatorWithdrawMethod;
use App\Models\WithdrawMethod;
use App\Models\WithdrawRequest;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $creator;
    protected string $token;
    protected CreatorWithdrawMethod $method;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'iso2' => 'eg',
            'phone_code' => '+20',
            'phone_min_length' => 11,
            'phone_max_length' => 11,
            'timezone' => 'Africa/Cairo',
        ]);

        $this->creator = Creator::factory()->create([
            'is_active' => true,
            'country_id' => $country->id,
            'available_balance' => 1000.00, // Sufficient balance
        ]);

        $this->token = $this->creator->createToken('test_token')->plainTextToken;

        $globalMethod = WithdrawMethod::create([
            'name' => ['en' => 'Bank', 'ar' => 'بنك'],
            'logo' => 'bank.png',
            'country_id' => $country->id,
            'slug' => 'bank',
        ]);

        $this->method = CreatorWithdrawMethod::create([
            'creator_id' => $this->creator->id,
            'withdraw_method_id' => $globalMethod->id,
            'is_default' => true,
        ]);

        Setting::create([
            'key' => 'withdrawal.min_amount',
            'value' => '500',
            'type' => 'float',
            'group' => 'financial',
        ]);
    }

    protected function withAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_can_list_withdraw_requests()
    {
        WithdrawRequest::create([
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $this->method->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->withAuth()
            ->getJson('/api/withdraw-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'amount',
                        'status',
                        'withdraw_method',
                        'creator_withdraw_method' => [
                            'id',
                            'is_default',
                            'method' => [
                                'id',
                                'name',
                                'slug',
                                'logo',
                                'is_active',
                            ],
                            'values',
                        ],
                        'created_at',
                        'updated_at',
                    ]
                ],
                'meta'
            ])
            ->assertJsonPath('data.0.withdraw_method', 'Bank')
            ->assertJsonPath('data.0.creator_withdraw_method.method.name', 'Bank');
    }

    public function test_can_filter_withdraw_requests_by_status()
    {
        WithdrawRequest::create([
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $this->method->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        WithdrawRequest::create([
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $this->method->id,
            'amount' => 600,
            'status' => 'completed',
        ]);

        $response = $this->withAuth()
            ->getJson('/api/withdraw-requests?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_create_withdraw_request()
    {
        $response = $this->withAuth()
            ->postJson('/api/withdraw-requests', [
                'creator_withdraw_method_id' => $this->method->id,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Withdrawal request created successfully.',
            ])
            ->assertJsonPath('data.withdraw_method', 'Bank')
            ->assertJsonPath('data.creator_withdraw_method.method.name', 'Bank');

        $this->assertDatabaseHas('withdraw_requests', [
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $this->method->id,
            'amount' => 1000.00,
            'status' => 'pending',
        ]);

        // Assert balance deducted
        $this->assertEquals(0, $this->creator->fresh()->available_balance);
    }

    public function test_cannot_create_withdraw_request_with_insufficient_balance()
    {
        $this->creator->update(['available_balance' => 200.00]);

        $response = $this->withAuth()
            ->postJson('/api/withdraw-requests', [
                'creator_withdraw_method_id' => $this->method->id,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_cannot_create_withdraw_request_with_invalid_method()
    {
        $response = $this->withAuth()
            ->postJson('/api/withdraw-requests', [
                'creator_withdraw_method_id' => 999, // Invalid
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_can_get_options()
    {
        $response = $this->withAuth()
            ->getJson('/api/withdraw-requests/filter-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'statuses',
                    'withdraw_methods',
                ]
            ]);
    }

    public function test_cannot_delete_withdraw_method_with_existing_requests()
    {
        WithdrawRequest::create([
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $this->method->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->withAuth()
            ->deleteJson("/api/my-withdraw-methods/{$this->method->id}");

        $response->assertStatus(400)
            ->assertJson([
                'status' => false,
                'message' => 'Cannot delete payment method because it has associated withdrawal requests.',
            ]);
    }
}
