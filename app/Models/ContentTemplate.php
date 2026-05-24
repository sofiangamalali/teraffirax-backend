<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasLogo;

class ContentTemplate extends Model
{
    use HasFactory, HasLogo;

    protected $fillable = [
        'creator_id',
        'social_integration_id',
        'name',
        'content',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected $attributes = [
        'is_default' => false,
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function socialIntegration()
    {
        return $this->belongsTo(SocialIntegration::class);
    }

    /**
     * Get the full URL of the platform's logo.
     *
     * @return string|null
     */
    public function getPlatformLogoUrlAttribute(): ?string
    {
        return $this->socialIntegration?->platform_logo_url;
    }
}
