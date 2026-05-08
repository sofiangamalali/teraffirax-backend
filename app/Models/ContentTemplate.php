<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'platform',
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


}
