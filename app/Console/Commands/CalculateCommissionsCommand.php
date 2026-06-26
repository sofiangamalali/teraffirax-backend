<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CommissionCalculationService;
use App\Models\CronRunLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CalculateCommissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and calculate all new commissions';

    /**
     * Execute the console command.
     */
    public function handle(CommissionCalculationService $service)
    {
        $runId = Str::uuid();
        $this->info("Starting commission calculation... Run ID: {$runId}");
        Log::channel('commission')->info("Starting commissions:calculate command. Run ID: {$runId}");

        $log = CronRunLog::create([
            'cron_name' => 'commissions:calculate',
            'run_id' => $runId,
            'started_at' => now(),
            'status' => 'partial', // temporary status
        ]);

        try {
            $processedCount = $service->execute();

            $log->update([
                'finished_at' => now(),
                'records_processed' => $processedCount,
                'status' => 'success',
            ]);

            $this->info("Successfully processed {$processedCount} commissions.");
            Log::channel('commission')->info("commissions:calculate command finished successfully. Run ID: {$runId}. Processed {$processedCount} commissions.");
        } catch (\Exception $e) {
            $log->update([
                'finished_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $this->error("Failed: " . $e->getMessage());
            Log::channel('commission')->error("commissions:calculate command failed. Run ID: {$runId}. Error: " . $e->getMessage(), ['exception' => $e]);
        }
    }
}
