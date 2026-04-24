<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso2',
        'phone_code',
        'phone_min_length',
        'phone_max_length',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'phone_min_length' => 'integer',
        'phone_max_length' => 'integer',
    ];
}
