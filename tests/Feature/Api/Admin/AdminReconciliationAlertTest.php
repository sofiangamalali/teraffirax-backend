<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Admin;
use App\Models\Creator;
use App\Models\Country;
use App\Models\ReconciliationAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReconciliationAlertTest extends TestCase
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

    public function test_non_admin_cannot_access_reconciliation_alerts_endpoint(): void
    {
        $response = $this->withCreatorAuth()->getJson('/api/admin/reconciliation-alerts');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_paginated_reconciliation_alerts(): void
    {
        // Create reconciliation alerts
        $alert1 = ReconciliationAlert::create([
            'creator_id' => $this->creator->id,
            'message' => 'Pending balance mismatch. Cache: 50.00, Recomputed: 0.00',
        ]);

        $alert2 = ReconciliationAlert::create([
            'creator_id' => $this->creator->id,
            'message' => 'Available balance mismatch. Cache: 100.00, Recomputed: 150.00',
        ]);

        $response = $this->withAdminAuth()->getJson('/api/admin/reconciliation-alerts?per_page=5');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'creator_id',
                        'creator' => [
                            'id',
                            'name',
                            'email',
                        ],
                        'message',
                        'created_at',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);

        $this->assertCount(2, $response->json('data'));
        // Alerts should be ordered latest first
        $this->assertEquals($alert2->id, $response->json('data.0.id'));
        $this->assertEquals($alert1->id, $response->json('data.1.id'));
    }

    public function test_admin_can_filter_reconciliation_alerts_by_creator_id(): void
    {
        $otherCreator = Creator::factory()->create([
            'country_id' => 1,
            'is_active' => true,
        ]);

        $alert1 = ReconciliationAlert::create([
            'creator_id' => $this->creator->id,
            'message' => 'Pending balance mismatch for creator 1',
        ]);

        $alert2 = ReconciliationAlert::create([
            'creator_id' => $otherCreator->id,
            'message' => 'Pending balance mismatch for creator 2',
        ]);

        $response = $this->withAdminAuth()->getJson("/api/admin/reconciliation-alerts?creator_id={$this->creator->id}");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($alert1->id, $response->json('data.0.id'));
    }
}
