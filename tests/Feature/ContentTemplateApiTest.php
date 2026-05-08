<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\Country;
use App\Models\SocialIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTemplateApiTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $creator;
    protected string $token;
    protected SocialIntegration $integration;

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

        $this->integration = SocialIntegration::create([
            'creator_id' => $this->creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'active',
            'name' => 'My Channel',
        ]);
    }

    protected function withAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_creator_can_list_templates(): void
    {
        $response = $this->withAuth()->getJson('/api/content-templates');

        $response->assertStatus(200);
    }

    public function test_creator_can_create_template(): void
    {
        $data = [
            'platform' => 'telegram',
            'name' => 'Promo Template',
            'content' => 'خصم {{discount}} على {{product_name}} اطلبه من هنا: {{link}}',
        ];

        $response = $this->withAuth()->postJson('/api/content-templates', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Promo Template')
            ->assertJsonPath('data.is_default', true);
    }

    public function test_creator_cannot_create_template_with_invalid_variables(): void
    {
        $data = [
            'platform' => 'telegram',
            'name' => 'Invalid Template',
            'content' => 'This has invalid variable {{invalid_var}}',
        ];

        $response = $this->withAuth()->postJson('/api/content-templates', $data);

        $response->assertStatus(422);
    }
}
