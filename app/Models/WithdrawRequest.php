<?php

namespace App\Models;

use App\Enums\WithdrawRequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasCurrency;

class WithdrawRequest extends Model
{
    use HasFactory, HasCurrency, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'creator_id',
        'creator_withdraw_method_id',
        'amount',
        'status',
        'requested_at',
        'reviewed_at',
        'completed_at',
    ];

    protected $casts = [
        'status' => WithdrawRequestStatusEnum::class,
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function withdrawMethod()
    {
        return $this->belongsTo(CreatorWithdrawMethod::class, 'creator_withdraw_method_id')->withTrashed();
    }
}
