<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Translatable\HasTranslations;

class WithdrawMethod extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['name'];

    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'country_id' => 'integer'
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(WithdrawMethodField::class)->orderBy('sort_order');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
