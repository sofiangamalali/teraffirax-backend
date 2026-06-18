<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SocialIntegration;
use App\Jobs\ProductSelectionJob;

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
        $integrations = SocialIntegration::where('status', 'active')
            ->where('platform', 'telegram')
            ->get();

        $this->info("Found {$integrations->count()} active Telegram integrations. Dispatching jobs...");

        foreach ($integrations as $integration) {
            ProductSelectionJob::dispatch(
                socialIntegrationId: $integration->id,
                externalId: $integration->external_id,
                creatorId: $integration->creator_id
            );
        }

        $this->info("All jobs dispatched successfully.");
    }
}
