<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedDailyClickJob extends Model
{
    protected $fillable = [
        'creator_id',
        'date',
        'error_message'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
