<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ResetPasswordDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $token,
        public readonly string $password,
        public readonly string $password_confirmation,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->input('email'),
            token: $request->input('token'),
            password: $request->input('password'),
            password_confirmation: $request->input('password_confirmation'),
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'token' => $this->token,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ];
    }
}
