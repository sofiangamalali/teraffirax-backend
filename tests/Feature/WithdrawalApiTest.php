<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\CreatorPaymentMethod;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_withdrawal_request()
    {
        $creator = Creator::factory()->create();
        $link = CreatorPaymentMethod::factory()->create(['creator_id' => $creator->id]);

        $data = [
            'creator_id' => $creator->id,
            'amount' => 500,
            'creator_payment_method_id' => $link->id
        ];

        $response = $this->postJson('/api/withdrawals', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('withdrawals', ['amount' => 500, 'status' => 'pending']);
    }

    public function test_can_update_withdrawal_status()
    {
        $withdrawal = Withdrawal::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/withdrawals/{$withdrawal->id}/status", [
            'status' => 'done'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('done', $withdrawal->refresh()->status);
    }

    public function test_can_list_withdrawals()
    {
        Withdrawal::factory()->count(3)->create();

        $response = $this->getJson('/api/withdrawals');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_cannot_change_done_withdrawal_to_pending()
    {
        $withdrawal = Withdrawal::factory()->create(['status' => 'done']);

        $response = $this->patchJson("/api/withdrawals/{$withdrawal->id}/status", [
            'status' => 'pending'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_cannot_update_unsupported_fields()
    {
        $withdrawal = Withdrawal::factory()->create();

        $response = $this->putJson("/api/withdrawals/{$withdrawal->id}", ['amount' => 9999]);

        $response->assertStatus(405);
    }

    public function test_cannot_delete_withdrawal()
    {
        $withdrawal = Withdrawal::factory()->create();

        $response = $this->deleteJson("/api/withdrawals/{$withdrawal->id}");

        $response->assertStatus(405);
    }
}
