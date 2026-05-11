<?php

namespace App\Models;

use App\Enums\WithdrawRequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'creator_withdraw_method_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'status' => WithdrawRequestStatusEnum::class,
        'amount' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function withdrawMethod()
    {
        return $this->belongsTo(CreatorWithdrawMethod::class, 'creator_withdraw_method_id');
    }
}
