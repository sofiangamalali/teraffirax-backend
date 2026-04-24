<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Creator;
use App\Models\DailyClick;

class CreatorObserver
{
    /**
     * Handle the Creator "created" event.
     */
    public function created(Creator $creator): void
    {
        DailyClick::create([
            'creator_id' => $creator->id,
            'date' => Carbon::now()->subDay()->toDateString(),
            'clicks_count' => 0,
            'total_snapshot' => 0,
        ]);
    }
}
