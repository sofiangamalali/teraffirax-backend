<?php

namespace App\Enums;

enum SettingKeyEnum: string
{
    case WITHDRAWAL_MIN_AMOUNT = 'withdrawal.min_amount';
    case MINIMUM_PRODUCT_COMMISSION = 'minimum_product_commission';
    
    /**
     * Get the group for a key.
     */
    public function group(): string
    {
        return match ($this) {
            self::WITHDRAWAL_MIN_AMOUNT => 'financial',
            self::MINIMUM_PRODUCT_COMMISSION => 'financial',
        };
    }
    
    /**
     * Get the type for a key.
     */
    public function type(): string
    {
        return match ($this) {
            self::WITHDRAWAL_MIN_AMOUNT => 'float',
            self::MINIMUM_PRODUCT_COMMISSION => 'float',
        };
    }
}
