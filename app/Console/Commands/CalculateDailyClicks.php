<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Creator;
use Illuminate\Console\Command;
use App\Services\DailyClickService;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Interfaces\FailedDailyClickJobRepositoryInterface;

class CalculateDailyClicks extends Command
{
    protected $signature = 'calculate:daily-clicks {date? : The date to calculate for (YYYY-MM-DD), defaults to yesterday}';
    protected $description = 'Calculates and records daily clicks for all creators based on lifetime totals.';

    public function handle(DailyClickService $service, FailedDailyClickJobRepositoryInterface $failureRepo)
    {
        $globalLock = Cache::lock("daily-clicks-job", 600);

        return $globalLock->get(function () use ($service, $failureRepo) {
            $dateString = $this->argument('date') ?: now()->subDay()->toDateString();
            $date = Carbon::parse($dateString);

            $this->info("Calculating daily clicks for {$date->toDateString()}...");

            $count = Creator::count();
            $this->info("Found {$count} creators to process.");

            Creator::chunk(100, function ($creators) use ($service, $date, $failureRepo) {
                    $this->info("Processing chunk of " . count($creators) . " creators.");
                    foreach ($creators as $creator) {
                        $lock = Cache::lock("calc-clicks-{$creator->id}", 600);

                        try {
                            $lock->block(10, function () use ($service, $creator, $date) {
                                            $service->calculateAndRecordDailyClicks($creator, $date);
                                        }
                                        );
                                    }
                                    catch (\Exception $e) {
                                        $failureRepo->create([
                                            'creator_id' => $creator->id,
                                            'date' => $date->toDateString(),
                                            'error_message' => $e->getMessage()
                                        ]);
                                        $this->error("Failed for Creator ID {$creator->id}: {$e->getMessage()}");
                                    }
                                }
                            }
                            );

                            $this->info('Daily clicks calculation completed.');
                        }) ?: $this->warn('Job is already running. Exiting.');
    }
}
