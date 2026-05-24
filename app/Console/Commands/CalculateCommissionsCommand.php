<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CommissionCalculationService;
use App\Models\CronRunLog;
use Illuminate\Support\Str;

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
        } catch (\Exception $e) {
            $log->update([
                'finished_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $this->error("Failed: " . $e->getMessage());
        }
    }
}
