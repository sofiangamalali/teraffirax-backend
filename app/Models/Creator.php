<?php

namespace App\Models;

use App\Traits\HasApiTokens;
use Database\Factories\CreatorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Creator extends Authenticatable
{
    /** @use HasFactory<CreatorFactory> */
    use HasFactory, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'gender',
        'balance',
        'amazon_track_id',
        'country_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'country_id' => 'integer',
        ];
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function trackingLinks()
    {
        return $this->hasMany(TrackingLink::class);
    }

    public function telegramChannels()
    {
        return $this->hasMany(TelegramChannel::class);
    }
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
