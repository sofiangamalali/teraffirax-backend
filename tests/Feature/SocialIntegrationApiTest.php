<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialIntegrationApiTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $creator;
    protected string $token;

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
    }

    protected function withAuth(): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function test_creator_can_list_integrations(): void
    {
        $response = $this->withAuth()->getJson('/api/social-integrations');
        
        $response->dump();

        $response->assertStatus(200);
    }

    public function test_creator_can_create_integration(): void
    {
        $data = [
            'platform' => 'telegram',
            'type' => 'channel',
            'name' => 'My Channel',
        ];

        $response = $this->withAuth()->postJson('/api/social-integrations', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'My Channel');
    }
}
