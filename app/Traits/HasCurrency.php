<?php

namespace App\Traits;

use App\Models\Creator;

trait HasCurrency
{
    /**
     * Get the currency of the country associated with the creator.
     *
     * @return string|null
     */
    public function getCurrencyAttribute(): ?string
    {
        // 1. If the model is the Creator itself
        if ($this instanceof Creator) {
            return $this->country?->currency;
        }

        // 2. If the model has a creator relationship
        if (method_exists($this, 'creator') && $this->creator) {
            return $this->creator->country?->currency;
        }

        // 3. If the model has a direct country relationship
        if (method_exists($this, 'country') && $this->country) {
            return $this->country->currency;
        }

        return null;
    }
}
