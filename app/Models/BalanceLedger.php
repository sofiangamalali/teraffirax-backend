<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_id',
        'performed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(AccountManager::class, 'performed_by');
    }
}
