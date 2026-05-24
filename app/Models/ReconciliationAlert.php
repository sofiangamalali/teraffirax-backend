<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReconciliationAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'message',
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
