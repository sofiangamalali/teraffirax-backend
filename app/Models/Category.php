<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the products for the category.
     * (Optional, but likely useful if products belong to categories)
     */

    public function creators()
    {
        return $this->belongsToMany(Creator::class);
    }
}
