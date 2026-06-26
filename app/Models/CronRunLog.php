<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronRunLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cron_name',
        'run_id',
        'started_at',
        'finished_at',
        'records_processed',
        'status',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'records_processed' => 'integer',
        'status' => \App\Enums\CronRunLogStatusEnum::class,
    ];
}
