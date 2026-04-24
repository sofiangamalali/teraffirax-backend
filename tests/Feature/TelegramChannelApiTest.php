<?php

namespace Tests\Feature;

use App\Models\Creator;
use App\Models\TelegramChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramChannelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_telegram_channels()
    {
        TelegramChannel::factory()->count(3)->create();

        $response = $this->getJson('/api/telegram-channels');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_telegram_channel_via_api()
    {
        $creator = Creator::factory()->create();

        $data = [
            'creator_id' => $creator->id,
            'name' => 'My TikTok Channel',
            'telegram_id' => '@mytokchannel',
            'link' => 'https://t.me/mytokchannel'
        ];

        $response = $this->postJson('/api/telegram-channels', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'My TikTok Channel');

        $this->assertDatabaseHas('telegram_channels', ['telegram_id' => '@mytokchannel']);
    }

    public function test_can_show_telegram_channel()
    {
        $channel = TelegramChannel::factory()->create();

        $response = $this->getJson("/api/telegram-channels/{$channel->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $channel->id);
    }

    public function test_can_update_telegram_channel()
    {
        $channel = TelegramChannel::factory()->create();

        $data = ['name' => 'Updated Name'];

        $response = $this->putJson("/api/telegram-channels/{$channel->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_can_delete_telegram_channel()
    {
        $channel = TelegramChannel::factory()->create();

        $response = $this->deleteJson("/api/telegram-channels/{$channel->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('telegram_channels', ['id' => $channel->id]);
    }
}
