<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Spatie\Translatable\HasTranslations;

class WithdrawMethodField extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['label', 'placeholder'];

    protected $fillable = [
        'withdraw_method_id',
        'name',
        'label',
        'type',
        'validation',
        'options',
        'placeholder',
        'sort_order'
    ];

    protected $casts = [
        'validation' => 'array',
        'options' => 'array',
        'sort_order' => 'integer'
    ];

    public function withdrawMethod(): BelongsTo
    {
        return $this->belongsTo(WithdrawMethod::class);
    }
}
