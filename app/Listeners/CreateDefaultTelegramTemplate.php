<?php

namespace App\Listeners;

use App\Events\CreatorRegistered;
use App\Models\ContentTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateDefaultTelegramTemplate
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

        $content = "{{discount}}% discount on {{product_name}} at {{product_price}} EGP\n\nOffer link: {{link}}";

        ContentTemplate::create([
            'creator_id' => $creator->id,
            'platform' => 'telegram',
            'name' => 'Default Template',
            'content' => $content,
            'is_default' => true,
        ]);
    }
}
