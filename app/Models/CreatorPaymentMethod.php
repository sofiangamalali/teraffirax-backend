<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorPaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'payment_method_id',
        'is_default',
        'details'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'details' => 'array'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
