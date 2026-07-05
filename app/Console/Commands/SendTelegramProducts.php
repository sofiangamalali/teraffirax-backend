<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Creator;
use App\Jobs\ProductSelectionJob;
use App\Enums\PlatformEnum;
use App\Enums\SocialIntegrationStatusEnum;

class SendTelegramProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-telegram-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch ProductSelectionJob for all active Telegram integrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $creators = Creator::where('is_active', true)
            ->with([
                'socialIntegrations' => function ($query) {
                    $query->where('status', SocialIntegrationStatusEnum::ACTIVE)
                        ->where('platform', PlatformEnum::TELEGRAM);
                }
            ])
            ->get();

        $dispatchedCount = 0;
        $batchSize = 4; // matches number of Gunicorn workers for flask 
        $delayPerBatch = 7; // seconds per batch (approximate validation time per request)

        foreach ($creators as $creator) {
            foreach ($creator->socialIntegrations as $integration) {
                $batchIndex = (int) ($dispatchedCount / $batchSize);
                $delaySeconds = $batchIndex * $delayPerBatch;

                ProductSelectionJob::dispatch(
                    socialIntegrationId: $integration->id,
                    externalId: $integration->external_id,
                    creatorId: $integration->creator_id
                )->delay(now()->addSeconds($delaySeconds));

                $dispatchedCount++;
            }
        }

        $this->info("Found {$dispatchedCount} active Telegram integrations for active creators. Dispatching jobs...");
        $this->info("All jobs dispatched successfully.");
    }
}
