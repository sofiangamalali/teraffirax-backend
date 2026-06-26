<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\TelegramPostStatusEnum;

class TelegramProductPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_integration_id',
        'product_id',
        'post_status',
        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'post_status' => TelegramPostStatusEnum::class,
    ];

    /**
     * Get the product that was posted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the social integration channel this product was posted to.
     */
    public function socialIntegration(): BelongsTo
    {
        return $this->belongsTo(SocialIntegration::class);
    }
}
