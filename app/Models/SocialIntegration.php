<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasLogo;
use App\Enums\PlatformEnum;
use App\Enums\IntegrationTypeEnum;
use App\Enums\SocialIntegrationStatusEnum;

class SocialIntegration extends Model
{
    use HasFactory, HasLogo;

    protected $fillable = [
        'creator_id',
        'platform',
        'type',
        'status',
        'external_id',
        'name',
        'username',
        'access_token',
        'metadata',
    ];

    protected $casts = [
        'platform' => PlatformEnum::class,
        'type' => IntegrationTypeEnum::class,
        'status' => SocialIntegrationStatusEnum::class,
        'metadata' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function contentTemplates()
    {
        return $this->hasMany(ContentTemplate::class);
    }

    /**
     * Get the full URL of the platform's logo.
     *
     * @return string|null
     */
    public function getPlatformLogoUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->platform, 'platforms');
    }
}
