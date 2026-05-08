<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Country;
use App\Models\Creator;
use App\Models\WithdrawMethod;
use App\Models\WithdrawMethodField;
use App\Models\CreatorWithdrawMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorWithdrawMethodApiTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $creator;
    protected Country $egypt;
    protected Country $usa;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->egypt = Country::create([
            'name' => ['en' => 'Egypt', 'ar' => 'مصر'],
            'iso2' => 'eg',
            'phone_code' => '+20',
            'phone_min_length' => 11,
            'phone_max_length' => 11,
        ]);

        $this->usa = Country::create([
            'name' => ['en' => 'USA', 'ar' => 'أمريكا'],
            'iso2' => 'us',
            'phone_code' => '+1',
            'phone_min_length' => 10,
            'phone_max_length' => 10,
        ]);

        $this->creator = Creator::factory()->create([
            'country_id' => $this->egypt->id,
            'is_active' => true,
        ]);

        $this->token = $this->creator->createToken('test_token')->plainTextToken;
    }

    protected function withAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_withdraw_methods()
    {
        $this->getJson('/api/withdraw-methods')->assertStatus(401);
    }

    /** @test */
    public function creator_can_list_available_methods_for_their_country()
    {
        WithdrawMethod::create([
            'country_id' => $this->egypt->id,
            'name' => ['en' => 'Vodafone Cash'],
            'slug' => 'vodafone-cash',
        ]);

        WithdrawMethod::create([
            'country_id' => $this->usa->id,
            'name' => ['en' => 'Stripe'],
            'slug' => 'stripe',
        ]);

        $response = $this->withAuth()->getJson('/api/withdraw-methods');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'vodafone-cash');
    }

    /** @test */
    public function creator_cannot_add_method_from_another_country()
    {
        $usaMethod = WithdrawMethod::create([
            'country_id' => $this->usa->id,
            'name' => ['en' => 'Stripe'],
            'slug' => 'stripe',
        ]);

        $response = $this->withAuth()->postJson('/api/my-withdraw-methods', [
            'withdraw_method_id' => $usaMethod->id,
            'values' => ['account' => 'test']
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['withdraw_method_id']);
    }

    /** @test */
    public function dynamic_validation_enforces_rules_correctly()
    {
        $method = WithdrawMethod::create([
            'country_id' => $this->egypt->id,
            'name' => ['en' => 'Wallet'],
            'slug' => 'wallet',
        ]);

        WithdrawMethodField::create([
            'withdraw_method_id' => $method->id,
            'name' => 'phone',
            'label' => ['en' => 'Phone'],
            'type' => 'text',
            'validation' => ['required' => true, 'min' => 11, 'max' => 11],
        ]);

        // Test missing required field
        $this->withAuth()->postJson('/api/my-withdraw-methods', [
            'withdraw_method_id' => $method->id,
            'values' => []
        ])->assertStatus(422)->assertJsonValidationErrors(['values.phone']);

        // Test invalid length
        $this->withAuth()->postJson('/api/my-withdraw-methods', [
            'withdraw_method_id' => $method->id,
            'values' => ['phone' => '123']
        ])->assertStatus(422)->assertJsonValidationErrors(['values.phone']);
    }



    /** @test */
    public function creator_can_successfully_add_and_delete_valid_method()
    {
        $method = WithdrawMethod::create([
            'country_id' => $this->egypt->id,
            'name' => ['en' => 'Wallet'],
            'slug' => 'wallet',
        ]);

        WithdrawMethodField::create([
            'withdraw_method_id' => $method->id,
            'name' => 'phone',
            'label' => ['en' => 'Phone'],
            'type' => 'text',
            'validation' => ['required' => true],
        ]);

        $response = $this->withAuth()->postJson('/api/my-withdraw-methods', [
            'withdraw_method_id' => $method->id,
            'values' => ['phone' => '01012345678'],
            'is_default' => true
        ]);

        $response->assertStatus(201);
        $accountId = $response->json('data.id');

        $this->assertDatabaseHas('creator_withdraw_method_values', [
            'field_value' => '01012345678'
        ]);

        // Delete
        $this->withAuth()->deleteJson("/api/my-withdraw-methods/{$accountId}")
            ->assertStatus(200);

        $this->assertSoftDeleted('creator_withdraw_methods', ['id' => $accountId]);
    }

    /** @test */
    public function first_method_is_always_default_even_if_not_requested()
    {
        $method = WithdrawMethod::create([
            'country_id' => $this->egypt->id,
            'name' => ['en' => 'Wallet'],
            'slug' => 'wallet',
        ]);

        WithdrawMethodField::create([
            'withdraw_method_id' => $method->id,
            'name' => 'dummy_field',
            'label' => ['en' => 'Dummy'],
            'type' => 'text',
            'validation' => [],
        ]);

        $response = $this->withAuth()->postJson('/api/my-withdraw-methods', [
            'withdraw_method_id' => $method->id,
            'values' => ['dummy_field' => '123'], 
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.is_default', true);
    }



    /** @test */
    public function deleting_default_assigns_new_default()
    {
        $method = WithdrawMethod::create([
            'country_id' => $this->egypt->id,
            'name' => ['en' => 'Wallet'],
            'slug' => 'wallet',
        ]);

        $account1 = CreatorWithdrawMethod::create([
            'creator_id' => $this->creator->id,
            'withdraw_method_id' => $method->id,
            'is_default' => true,
        ]);

        $account2 = CreatorWithdrawMethod::create([
            'creator_id' => $this->creator->id,
            'withdraw_method_id' => $method->id,
            'is_default' => false,
        ]);

        $this->withAuth()->deleteJson("/api/my-withdraw-methods/{$account1->id}")
            ->assertStatus(200);

        $this->assertTrue($account2->fresh()->is_default);
    }
}
