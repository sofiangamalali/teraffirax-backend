<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class LoginDTO
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            identifier: $request->input('identifier'),
            password: $request->input('password'),
        );
    }
}
