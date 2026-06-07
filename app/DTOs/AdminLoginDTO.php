<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class AdminLoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    /**
     * Create a DTO from request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->input('email'),
            password: $request->input('password'),
        );
    }
}
