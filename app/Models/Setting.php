<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    /**
     * Get the casted value.
     * This can be used if we want to cast directly on the model,
     * but the service will also handle it as per requirements.
     */
    public function getCastedValueAttribute()
    {
        return $this->castValue($this->value, $this->type);
    }

    /**
     * Cast value based on type.
     */
    public static function castValue(?string $value, string $type)
    {
        if (is_null($value)) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
