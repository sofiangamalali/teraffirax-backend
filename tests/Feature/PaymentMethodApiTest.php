<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_payment_methods()
    {
        PaymentMethod::factory()->count(3)->create();

        $response = $this->getJson('/api/payment-methods');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_payment_method_via_api()
    {
        $data = [
            'name' => 'Vodafone Cash',
            'type' => 'wallet',
            'details' => ['phone' => '01012345678'],
            'is_active' => true
        ];

        $response = $this->postJson('/api/payment-methods', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Vodafone Cash');

        $this->assertDatabaseHas('payment_methods', ['name' => 'Vodafone Cash']);
    }

    public function test_can_show_payment_method()
    {
        $method = PaymentMethod::factory()->create();

        $response = $this->getJson("/api/payment-methods/{$method->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $method->id);
    }

    public function test_can_update_payment_method()
    {
        $method = PaymentMethod::factory()->create();

        $data = ['name' => 'Updated Payment Name'];

        $response = $this->putJson("/api/payment-methods/{$method->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Payment Name');
    }

    public function test_can_soft_delete_payment_method()
    {
        $method = PaymentMethod::factory()->create();

        $response = $this->deleteJson("/api/payment-methods/{$method->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('payment_methods', ['id' => $method->id]);
    }
}
