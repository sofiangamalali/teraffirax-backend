<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'vendor_id',
        'date',
        'clicks_count',
        'items_count',
        'returned_count',
        'revenue_amount',
        'commission_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'clicks_count' => 'integer',
        'items_count' => 'integer',
        'returned_count' => 'integer',
        'revenue_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
