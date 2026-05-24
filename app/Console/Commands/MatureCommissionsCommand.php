<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CommissionMaturationService;
use App\Models\CronRunLog;
use Illuminate\Support\Str;

class MatureCommissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:mature';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and mature calculated commissions older than 90 days';

    /**
     * Execute the console command.
     */
    public function handle(CommissionMaturationService $service)
    {
        $runId = Str::uuid();
        $this->info("Starting commission maturation... Run ID: {$runId}");

        $log = CronRunLog::create([
            'cron_name' => 'commissions:mature',
            'run_id' => $runId,
            'started_at' => now(),
            'status' => 'partial',
        ]);

        try {
            $processedCount = $service->execute();

            $log->update([
                'finished_at' => now(),
                'records_processed' => $processedCount,
                'status' => 'success',
            ]);

            $this->info("Successfully matured {$processedCount} commissions.");
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
