<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReconciliationService;
use App\Models\CronRunLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ReconcileBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balances:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute balances and compare with creator caches';

    /**
     * Execute the console command.
     */
    public function handle(ReconciliationService $service)
    {
        $runId = Str::uuid();
        $this->info("Starting balance reconciliation... Run ID: {$runId}");
        Log::channel('commission')->info("Starting balances:reconcile command. Run ID: {$runId}");

        $log = CronRunLog::create([
            'cron_name' => 'balances:reconcile',
            'run_id' => $runId,
            'started_at' => now(),
            'status' => 'partial',
        ]);

        try {
            $alertsGenerated = $service->execute();

            $log->update([
                'finished_at' => now(),
                'records_processed' => $alertsGenerated,
                'status' => 'success',
            ]);

            $this->info("Reconciliation complete. Generated {$alertsGenerated} alerts.");
            Log::channel('commission')->info("balances:reconcile command finished successfully. Run ID: {$runId}. Alerts generated: {$alertsGenerated}");
        } catch (\Exception $e) {
            $log->update([
                'finished_at' => now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $this->error("Failed: " . $e->getMessage());
            Log::channel('commission')->error("balances:reconcile command failed. Run ID: {$runId}. Error: " . $e->getMessage(), ['exception' => $e]);
        }
    }
}
