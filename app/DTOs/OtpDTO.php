<?php

namespace App\DTOs;

use Carbon\Carbon;

class OtpDTO
{
    public function __construct(
        public ?string $email = null,
        public ?string $otp = null,
        public ?string $type = null,
        public ?Carbon $expires_at = null,
        public ?bool $is_used = false,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'otp' => $this->otp,
            'type' => $this->type,
            'expires_at' => $this->expires_at,
            'is_used' => $this->is_used,
        ], fn($value) => $value !== null);
    }
}
