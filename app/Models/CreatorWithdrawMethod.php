<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class CreatorWithdrawMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'withdraw_method_id',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function withdrawMethod(): BelongsTo
    {
        return $this->belongsTo(WithdrawMethod::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(CreatorWithdrawMethodValue::class);
    }

    public function withdrawRequests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class, 'creator_withdraw_method_id');
    }
}
