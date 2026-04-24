<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ResetPasswordWithOtpDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $otp,
        public readonly string $password,
    ) {
    }

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->input('email'),
            otp: $request->input('otp'),
            password: $request->input('password'),
        );
    }
}
