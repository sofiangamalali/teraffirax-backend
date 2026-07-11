<?php

namespace Tests\Feature;

use App\Models\{Category, Country, Creator, Otp, Vendor, CreatorVendorTracking};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Country::factory()->create(['id' => 1]);
    }

    public function test_can_fetch_registration_data()
    {
        Category::factory()->count(3)->create();
        Country::factory()->count(2)->create();

        $response = $this->getJson('/api/auth/registration-data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'countries',
                    'categories'
                ]
            ]);
        
        $this->assertCount(3, $response->json('data.countries')); // 1 from setup + 2 here
        $this->assertCount(3, $response->json('data.categories'));
    }

    public function test_can_register_creator_with_categories()
    {
        \Illuminate\Support\Facades\Mail::fake([\App\Mail\AdminCreatorRegisteredNotificationMail::class]);

        \App\Models\Admin::create([
            'name' => 'System Admin',
            'email' => 'admin@teraffirax.com',
            'password' => bcrypt('password123'),
        ]);

        $categories = Category::factory()->count(2)->create();
        $vendor = Vendor::factory()->create(['country_id' => 1, 'is_active' => true]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'gender' => 'male',
            'country_id' => 1,
            'categories' => $categories->pluck('id')->toArray(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('creators', ['email' => 'john@example.com']);
        $this->assertDatabaseCount('category_creator', 2);

        $creator = Creator::where('email', 'john@example.com')->first();
        $this->assertNotNull($creator);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\AdminCreatorRegisteredNotificationMail::class, function ($mail) use ($creator) {
            return $mail->creator->id === $creator->id && $mail->hasTo('admin@teraffirax.com');
        });

        $this->assertDatabaseHas('social_integrations', [
            'creator_id' => $creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'pending',
            'name' => 'Test Channel',
            'username' => $creator->name . '_telegraam',
            'external_id' => $creator->id . '_telegraam',
        ]);

        $integration = $creator->socialIntegrations()->first();
        $this->assertNotNull($integration);

        $this->assertDatabaseHas('content_templates', [
            'creator_id' => $creator->id,
            'social_integration_id' => $integration->id,
            'is_default' => true,
            'name' => 'Default Template',
            'content' => "{{product_name}} بسعر {{product_price}} \n\nرابط العرض: {{link}}",
        ]);

        $this->assertDatabaseHas('creator_vendor_tracking', [
            'creator_id' => $creator->id,
            'vendor_id' => $vendor->id,
            'track_id' => '',
            'is_active' => false,
        ]);

        // Create a vendor in a different country and assert it is NOT linked to the creator
        $otherCountry = Country::factory()->create(['id' => 2]);
        $otherVendor = Vendor::factory()->create(['country_id' => $otherCountry->id, 'is_active' => true]);

        $this->assertDatabaseMissing('creator_vendor_tracking', [
            'creator_id' => $creator->id,
            'vendor_id' => $otherVendor->id,
        ]);
    }

    public function test_can_login_with_email()
    {
        $creator = Creator::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'identifier' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);
    }

    public function test_can_login_with_phone()
    {
        $creator = Creator::factory()->create([
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'identifier' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);
    }

    public function test_forget_password_flow()
    {
        $creator = Creator::factory()->create(['email' => 'john@example.com']);

        // 1. Request Forgot Password
        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'john@example.com']);
        $response->assertStatus(200);

        $otp = Otp::where('email', 'john@example.com')->first();
        $this->assertNotNull($otp);

        // 2. Verify OTP
        $response = $this->postJson('/api/auth/verify-otp', [
            'email' => 'john@example.com',
            'otp' => $otp->otp,
            'type' => 'password_reset'
        ]);
        $response->assertStatus(200);

        // 3. Reset Password
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'john@example.com',
            'otp' => $otp->otp,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $response->assertStatus(200);

        $creator->refresh();
        $this->assertTrue(Hash::check('newpassword123', $creator->password));
    }

    public function test_can_refresh_token()
    {
        $creator = Creator::factory()->create();
        $refreshToken = $creator->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;

        $response = $this->postJson('/api/auth/refresh-token', [
            'refresh_token' => $refreshToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token', 'refresh_token']]);
    }
}
