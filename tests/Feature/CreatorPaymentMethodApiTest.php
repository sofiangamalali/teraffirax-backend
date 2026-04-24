<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\CreatorPaymentMethod;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorPaymentMethodApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_link_payment_method_to_creator()
    {
        $creator = Creator::factory()->create();
        $method = PaymentMethod::factory()->create();

        $data = [
            'creator_id' => $creator->id,
            'payment_method_id' => $method->id,
            'is_default' => true,
            'details' => ['phone' => '01000000000']
        ];

        $response = $this->postJson('/api/creator-payment-methods', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('creator_payment_methods', ['creator_id' => $creator->id, 'is_default' => true]);
    }

    public function test_setting_default_unsets_others()
    {
        $creator = Creator::factory()->create();
        
        // Create first default
        CreatorPaymentMethod::factory()->create(['creator_id' => $creator->id, 'is_default' => true]);
        
        // Create second default via API
        $method2 = PaymentMethod::factory()->create();
        $data = [
            'creator_id' => $creator->id,
            'payment_method_id' => $method2->id,
            'is_default' => true,
            'details' => ['phone' => '01111111111']
        ];

        $this->postJson('/api/creator-payment-methods', $data);

        $this->assertEquals(1, CreatorPaymentMethod::where('creator_id', $creator->id)->where('is_default', true)->count());
    }

    public function test_can_list_creator_methods()
    {
        $creator = Creator::factory()->create();
        CreatorPaymentMethod::factory()->count(2)->create(['creator_id' => $creator->id]);

        $response = $this->getJson("/api/creator-payment-methods?creator_id={$creator->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_update_link_details()
    {
        $link = CreatorPaymentMethod::factory()->create(['is_default' => false]);

        $response = $this->putJson("/api/creator-payment-methods/{$link->id}", [
            'is_default' => true,
            'details' => ['new' => 'data']
        ]);

        $response->assertStatus(200);
        $this->assertTrue($link->refresh()->is_default);
    }

    public function test_can_delete_link()
    {
        $link = CreatorPaymentMethod::factory()->create();

        $response = $this->deleteJson("/api/creator-payment-methods/{$link->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('creator_payment_methods', ['id' => $link->id]);
    }
}
