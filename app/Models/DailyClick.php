<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'date',
        'clicks_count',
        'total_snapshot'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
