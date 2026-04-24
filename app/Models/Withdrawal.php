<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'amount',
        'status',
        'creator_payment_method_id'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(CreatorPaymentMethod::class , 'creator_payment_method_id');
    }
}
