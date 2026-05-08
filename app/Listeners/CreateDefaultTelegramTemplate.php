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

        $content = "خصم {{discount}}% على {{product_name}} بسعر {{product_price}} ج\n\nلينك العرض: {{link}}";

        ContentTemplate::create([
            'creator_id' => $creator->id,
            'platform' => 'telegram',
            'name' => 'القالب الافتراضي',
            'content' => $content,
            'is_default' => true,
        ]);
    }
}
