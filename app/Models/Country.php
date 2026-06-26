<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name', 'currency'];

    protected $fillable = [
        'name',
        'iso2',
        'phone_code',
        'phone_min_length',
        'phone_max_length',
        'icon',
        'is_active',
        'currency',
        'timezone',
        'language',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'phone_min_length' => 'integer',
        'phone_max_length' => 'integer',
    ];
}
