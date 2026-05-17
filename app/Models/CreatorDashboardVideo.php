<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorDashboardVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_url',
        'thumbnail_url',
        'country_id',
        'views_count',
        'likes_count',
        'comments_count',
        'shares_count',
        'total_orders',
        'total_commission',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'total_orders' => 'integer',
        'total_commission' => 'decimal:2',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
