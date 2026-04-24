<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelegramChannel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'name',
        'telegram_id',
        'link'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
