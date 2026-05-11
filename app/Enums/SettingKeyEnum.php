<?php

namespace App\Enums;

enum SettingKeyEnum: string
{
    case WITHDRAWAL_MIN_AMOUNT = 'withdrawal.min_amount';
    
    /**
     * Get the group for a key.
     */
    public function group(): string
    {
        return match ($this) {
            self::WITHDRAWAL_MIN_AMOUNT => 'financial',
        };
    }
    
    /**
     * Get the type for a key.
     */
    public function type(): string
    {
        return match ($this) {
            self::WITHDRAWAL_MIN_AMOUNT => 'float',
        };
    }
}
