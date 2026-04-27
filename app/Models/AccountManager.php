<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountManager extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    public function creators()
    {
        return $this->hasMany(Creator::class);
    }
}
