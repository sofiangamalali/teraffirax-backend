<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorWithdrawMethodValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_withdraw_method_id',
        'field_name',
        'field_value'
    ];

    public function creatorWithdrawMethod(): BelongsTo
    {
        return $this->belongsTo(CreatorWithdrawMethod::class);
    }
}
