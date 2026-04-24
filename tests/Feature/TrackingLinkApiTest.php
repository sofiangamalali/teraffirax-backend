<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\Product;
use App\Models\TrackingLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_tracking_link()
    {
        $creator = Creator::factory()->create();
        $product = Product::factory()->create();
        
        $data = [
            'creator_id'   => $creator->id,
            'product_id'   => $product->id,
            'redirect_url' => 'https://google.com'
        ];

        $response = $this->postJson('/api/tracking-links', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.redirect_url', 'https://google.com')
            ->assertJsonStructure(['data' => ['slug', 'short_url']]);

        $this->assertDatabaseHas('tracking_links', [
            'creator_id' => $creator->id,
            'redirect_url' => 'https://google.com'
        ]);
    }

    public function test_cannot_create_duplicate_tracking_link()
    {
        $creator = Creator::factory()->create();
        $product = Product::factory()->create();
        
        $link = TrackingLink::factory()->create([
            'creator_id' => $creator->id,
            'product_id' => $product->id,
            'slug'       => 'EXISTING'
        ]);

        $data = [
            'creator_id'   => $creator->id,
            'product_id'   => $product->id,
            'redirect_url' => 'https://new-url.com'
        ];

        $response = $this->postJson('/api/tracking-links', $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'EXISTING')
            ->assertJsonPath('message', 'The product already exists for this creator');
    }

    public function test_redirection_works_and_increments_clicks()
    {
        $link = TrackingLink::factory()->create([
            'id' => 10, // Use fixed ID to test service integration
            'redirect_url' => 'https://example.com'
        ]);
        
        // Manual slug generation for the test model if factory didn't do it right
        // But our service does it. Let's use the factory slug for initial check, 
        // OR better, create via API to get a "real" slug.
        
        $data = [
            'creator_id'   => Creator::factory()->create()->id,
            'product_id'   => Product::factory()->create()->id,
            'redirect_url' => 'https://my-redirect.com'
        ];
        
        $createResponse = $this->postJson('/api/tracking-links', $data);
        $slug = $createResponse->json('data.slug');

        $response = $this->get('/' . $slug);

        $response->assertRedirect('https://my-redirect.com');
        
        $this->assertEquals(1, TrackingLink::where('slug', $slug)->first()->clicks_count);
    }

    public function test_invalid_slug_returns_404()
    {
        $response = $this->get('/invalid-slug-123');

        $response->assertStatus(404);
    }
}
