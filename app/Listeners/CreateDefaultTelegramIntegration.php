<?php

namespace App\Listeners;

use App\Events\CreatorRegistered;
use App\Models\SocialIntegration;
use App\Models\ContentTemplate;
use Illuminate\Support\Facades\Log;

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

        try {
            $integration = SocialIntegration::updateOrCreate(
                [
                    'creator_id' => $creator->id,
                    'platform' => 'telegram',
                    'type' => 'channel',
                ],
                [
                    'status' => 'active',
                    'name' => 'Test Channel',
                    'username' => $creator->name . '_telegraam',
                    'external_id' => $creator->id . '_telegraam',
                ]
            );

            ContentTemplate::updateOrCreate(
                [
                    'creator_id' => $creator->id,
                    'social_integration_id' => $integration->id,
                    'is_default' => true,
                ],
                [
                    'name' => 'Default Template',
                    'content' => "{{product_name}} بسعر {{product_price}} \n\nرابط العرض: {{link}}",
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to create default telegram integration/template for creator ' . $creator->id . ': ' . $e->getMessage());
        }
    }
}
