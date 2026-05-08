<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialIntegration extends Model
{
    use HasFactory;

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
}
