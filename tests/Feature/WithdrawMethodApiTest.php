<?php

namespace Tests\Feature;

use App\Models\WithdrawMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawMethodApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_withdraw_methods()
    {
        WithdrawMethod::factory()->count(3)->create();

        $response = $this->getJson('/api/withdraw-methods');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_withdraw_method_via_api()
    {
        $data = [
            'name' => 'Vodafone Cash',
            'type' => 'wallet',
            'details' => ['phone' => '01012345678'],
            'is_active' => true
        ];

        $response = $this->postJson('/api/withdraw-methods', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Vodafone Cash');

        $this->assertDatabaseHas('withdraw_methods', ['name' => 'Vodafone Cash']);
    }

    public function test_can_show_withdraw_method()
    {
        $method = WithdrawMethod::factory()->create();

        $response = $this->getJson("/api/withdraw-methods/{$method->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $method->id);
    }

    public function test_can_update_withdraw_method()
    {
        $method = WithdrawMethod::factory()->create();

        $data = ['name' => 'Updated Payment Name'];

        $response = $this->putJson("/api/withdraw-methods/{$method->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Payment Name');
    }

    public function test_can_soft_delete_withdraw_method()
    {
        $method = WithdrawMethod::factory()->create();

        $response = $this->deleteJson("/api/withdraw-methods/{$method->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('withdraw_methods', ['id' => $method->id]);
    }
}
