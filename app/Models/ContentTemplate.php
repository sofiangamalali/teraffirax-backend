<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTemplate extends Model
{
    use HasFactory;

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

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }

    public function socialIntegration()
    {
        return $this->belongsTo(SocialIntegration::class);
    }
}
