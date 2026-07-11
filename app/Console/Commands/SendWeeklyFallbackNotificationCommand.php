<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeeklyFallbackService;
use Illuminate\Support\Facades\Log;

class SendWeeklyFallbackNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:weekly-fallback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run weekly inactivity fallback nudges and 30-day manual re-engagement checks';

    /**
     * Execute the console command.
     */
    public function handle(WeeklyFallbackService $service): int
    {
        $this->info('Starting Weekly Fallback and inactivity checks...');
        Log::channel('weekly_fallback')->info('Starting notifications:weekly-fallback command.');

        try {
            $sentCount = $service->execute();
            $this->info("Successfully sent {$sentCount} Weekly Fallback nudge emails.");
            Log::channel('weekly_fallback')->info("notifications:weekly-fallback command finished successfully. Sent {$sentCount} nudges.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            Log::channel('weekly_fallback')->error('notifications:weekly-fallback command failed. Error: ' . $e->getMessage(), ['exception' => $e]);
            return Command::FAILURE;
        }
    }
}
