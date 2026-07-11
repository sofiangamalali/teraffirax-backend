<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\Vendor;
use App\Models\CreatorVendorTracking;
use App\Models\TrackingLink;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLinkApiTest extends TestCase
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

    public function test_can_create_tracking_link()
    {
        $vendor = Vendor::create([
            'name' => 'Amazon',
            'domain' => 'amazon.eg',
            'country_id' => $this->creator->country_id
        ]);
        
        CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $vendor->id,
            'track_id' => 'mytag-21'
        ]);

        $data = [
            'redirect_url' => 'https://www.amazon.eg/dp/B0FMR7H8FT'
        ];

        $response = $this->withAuth()
                         ->postJson('/api/tracking-links', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.redirect_url', 'https://www.amazon.eg/dp/B0FMR7H8FT?tag=mytag-21')
            ->assertJsonStructure(['data' => ['slug', 'short_url']]);

        $this->assertDatabaseHas('tracking_links', [
            'creator_id' => $this->creator->id,
            'redirect_url' => 'https://www.amazon.eg/dp/B0FMR7H8FT?tag=mytag-21'
        ]);
    }

    public function test_cannot_create_tracking_link_for_unsupported_vendor()
    {
        $data = [
            'redirect_url' => 'https://evil-amazon.eg/dp/B0FMR7H8FT'
        ];

        $response = $this->withAuth()
                         ->postJson('/api/tracking-links', $data);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'This product URL does not belong to any supported vendor.');
    }

    public function test_cannot_create_tracking_link_if_not_subscribed_to_vendor()
    {
        $vendor = Vendor::create([
            'name' => 'Amazon',
            'domain' => 'amazon.eg',
            'country_id' => $this->creator->country_id
        ]);

        $data = [
            'redirect_url' => 'https://www.amazon.eg/dp/B0FMR7H8FT'
        ];

        $response = $this->withAuth()
                         ->postJson('/api/tracking-links', $data);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Creator is not associated with this vendor or has no tracking ID.');
    }

    public function test_redirection_works_and_increments_clicks()
    {
        $vendor = Vendor::create([
            'name' => 'Amazon',
            'domain' => 'amazon.eg',
            'country_id' => $this->creator->country_id
        ]);
        
        CreatorVendorTracking::create([
            'creator_id' => $this->creator->id,
            'vendor_id' => $vendor->id,
            'track_id' => 'mytag-21'
        ]);

        $data = [
            'redirect_url' => 'https://www.amazon.eg/dp/B0FMR7H8FT'
        ];

        $createResponse = $this->withAuth()
                               ->postJson('/api/tracking-links', $data);

        $slug = $createResponse->json('data.slug');

        $response = $this->get('/' . $slug);

        $response->assertRedirect('https://www.amazon.eg/dp/B0FMR7H8FT?tag=mytag-21');
        
        $this->assertEquals(1, TrackingLink::where('slug', $slug)->first()->clicks_count);
    }

    public function test_invalid_slug_returns_404()
    {
        $response = $this->get('/invalid-slug-123');

        $response->assertStatus(404);
    }

    public function test_can_list_only_own_tracking_links_sorted_descending()
    {
        $otherCreator = Creator::factory()->create([
            'country_id' => 1,
            'is_active' => true,
        ]);

        // Links for the authenticated creator
        $link1 = TrackingLink::create([
            'creator_id' => $this->creator->id,
            'slug' => 'slugabc1',
            'redirect_url' => 'https://www.amazon.eg/dp/B0FMR7H8FT?tag=mytag-21',
        ]);
        $link1->created_at = now()->subMinutes(10);
        $link1->save();

        $link2 = TrackingLink::create([
            'creator_id' => $this->creator->id,
            'slug' => 'slugabc2',
            'redirect_url' => 'https://www.amazon.eg/dp/B0FMR7H8FT?tag=mytag-21',
        ]);
        $link2->created_at = now();
        $link2->save();

        // Link for another creator
        $linkOther = TrackingLink::create([
            'creator_id' => $otherCreator->id,
            'slug' => 'slugother',
            'redirect_url' => 'https://www.amazon.eg/dp/B0FMR7H8FT?tag=mytag-21',
            'created_at' => now(),
        ]);

        $response = $this->withAuth()->getJson('/api/tracking-links');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify only own links are returned
        $this->assertCount(2, $data);

        // Verify descending order (newest first)
        $this->assertStringContainsString($link2->slug, $data[0]['short_url']);
        $this->assertStringContainsString($link1->slug, $data[1]['short_url']);
    }
}
