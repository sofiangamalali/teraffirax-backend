<?php

namespace App\Listeners;

use App\Events\CreatorRegistered;
use App\Models\SocialIntegration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDefaultTelegramIntegration
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CreatorRegistered $event): void
    {
        $creator = $event->creator;

        SocialIntegration::create([
            'creator_id' => $creator->id,
            'platform' => 'telegram',
            'type' => 'channel',
            'status' => 'pending',
            'name' => 'My Telegram Channel',
        ]);
    }
}
