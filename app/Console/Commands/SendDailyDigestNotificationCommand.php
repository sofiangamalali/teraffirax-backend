<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailyDigestService;
use Illuminate\Support\Facades\Log;

class SendDailyDigestNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:daily-digest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily commission activity digest to creators';

    /**
     * Execute the console command.
     */
    public function handle(DailyDigestService $service): int
    {
        $this->info('Starting Daily Digest notifications flow...');
        Log::channel('daily_digest')->info('Starting notifications:daily-digest command.');

        try {
            $sentCount = $service->execute();
            $this->info("Successfully sent {$sentCount} Daily Digest emails.");
            Log::channel('daily_digest')->info("notifications:daily-digest command finished successfully. Sent {$sentCount} digests.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            Log::channel('daily_digest')->error('notifications:daily-digest command failed. Error: ' . $e->getMessage(), ['exception' => $e]);
            return Command::FAILURE;
        }
    }
}
