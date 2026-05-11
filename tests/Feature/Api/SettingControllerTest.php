<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Models\Creator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Creator $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Creator::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_settings_by_group(): void
    {
        Setting::create(['key' => 'site.name', 'value' => 'My App', 'type' => 'string', 'group' => 'general']);

        $response = $this->getJson('/api/settings?group=general');

        $response->assertStatus(200)
            ->assertJsonFragment(['site.name' => 'My App']);
    }

    public function test_can_create_setting(): void
    {
        $data = [
            'key' => 'telegram.max_channels',
            'value' => '5',
            'type' => 'integer',
            'group' => 'features',
        ];

        $response = $this->postJson('/api/settings', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['key' => 'telegram.max_channels']);

        $this->assertDatabaseHas('settings', ['key' => 'telegram.max_channels']);
    }

    public function test_can_show_setting(): void
    {
        Setting::create(['key' => 'site.name', 'value' => 'My App', 'type' => 'string', 'group' => 'general']);

        $response = $this->getJson('/api/settings/site.name');

        $response->assertStatus(200)
            ->assertJsonFragment(['key' => 'site.name']);
    }

    public function test_can_update_setting(): void
    {
        Setting::create(['key' => 'site.name', 'value' => 'My App', 'type' => 'string', 'group' => 'general']);
        $data = ['value' => 'New Name'];

        $response = $this->putJson('/api/settings/site.name', $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['value' => 'New Name']);
    }

    public function test_can_delete_setting(): void
    {
        Setting::create(['key' => 'site.name', 'value' => 'My App', 'type' => 'string', 'group' => 'general']);

        $response = $this->deleteJson('/api/settings/site.name');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('settings', ['key' => 'site.name']);
    }
}
