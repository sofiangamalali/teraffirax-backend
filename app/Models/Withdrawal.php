<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\WithdrawalStatusEnum;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'amount',
        'status',
        'creator_withdraw_method_id'
    ];

    protected $casts = [
        'status' => WithdrawalStatusEnum::class,
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function withdrawMethod()
    {
        return $this->belongsTo(CreatorWithdrawMethod::class , 'creator_withdraw_method_id');
    }
}
