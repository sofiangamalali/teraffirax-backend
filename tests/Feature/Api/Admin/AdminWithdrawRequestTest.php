<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Creator;
use App\Models\Country;
use App\Models\CreatorWithdrawMethod;
use App\Models\WithdrawMethod;
use App\Models\WithdrawRequest;
use App\Models\BalanceLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWithdrawRequestTest extends TestCase
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

        \App\Models\AccountManager::create([
            'id' => 1,
            'name' => 'System Admin',
            'email' => 'admin@test.com',
            'phone' => '12345678',
        ]);

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

    public function test_non_admin_cannot_access_admin_withdraw_request_endpoints(): void
    {
        $response = $this->withCreatorAuth()->getJson('/api/admin/withdraw-requests');
        $response->assertStatus(403);

        $response = $this->withCreatorAuth()->patchJson('/api/admin/withdraw-requests/some-uuid/status', [
            'status' => 'approved',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_view_paginated_withdraw_requests(): void
    {
        $method = WithdrawMethod::create(['name' => 'Bank Transfer', 'slug' => 'bank', 'is_active' => true, 'country_id' => 1]);
        $creatorMethod = CreatorWithdrawMethod::create([
            'creator_id' => $this->creator->id,
            'withdraw_method_id' => $method->id,
            'is_default' => true,
        ]);

        $request = WithdrawRequest::create([
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $creatorMethod->id,
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        $response = $this->withAdminAuth()->getJson('/api/admin/withdraw-requests?per_page=5');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'creator_id',
                        'amount',
                        'status',
                        'requested_at',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($request->id, $response->json('data.0.id'));
    }

    public function test_admin_can_complete_and_reject_withdraw_request(): void
    {
        $method = WithdrawMethod::create(['name' => 'Bank Transfer', 'slug' => 'bank', 'is_active' => true, 'country_id' => 1]);
        $creatorMethod = CreatorWithdrawMethod::create([
            'creator_id' => $this->creator->id,
            'withdraw_method_id' => $method->id,
            'is_default' => true,
        ]);

        $request = WithdrawRequest::create([
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $creatorMethod->id,
            'amount' => 500.00,
            'status' => 'pending',
        ]);

        // 1. Complete the request directly from pending
        $response = $this->withAdminAuth()->patchJson("/api/admin/withdraw-requests/{$request->id}/status", [
            'status' => 'completed',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('withdraw_requests', [
            'id' => $request->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('creators', [
            'id' => $this->creator->id,
            'withdrawn_balance' => 500.00,
        ]);

        // 2. Reject a pending request
        $request2 = WithdrawRequest::create([
            'creator_id' => $this->creator->id,
            'creator_withdraw_method_id' => $creatorMethod->id,
            'amount' => 300.00,
            'status' => 'pending',
        ]);
        $this->creator->update(['available_balance' => 0]);

        $response = $this->withAdminAuth()->patchJson("/api/admin/withdraw-requests/{$request2->id}/status", [
            'status' => 'rejected',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('withdraw_requests', [
            'id' => $request2->id,
            'status' => 'rejected',
        ]);
        $this->assertDatabaseHas('creators', [
            'id' => $this->creator->id,
            'available_balance' => 300.00,
        ]);
    }
}
