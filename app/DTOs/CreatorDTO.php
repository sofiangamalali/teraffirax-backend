<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CreatorDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?string $gender = null,
        public readonly ?int $country_id = null,
        public readonly ?array $categories = null,
    ) {
    }

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            phone: $request->input('phone'),
            email: $request->input('email'),
            password: $request->input('password'),
            gender: $request->input('gender'),
            country_id: $request->input('country_id') ? (int) $request->input('country_id') : null,
            categories: $request->has('categories') ? $request->input('categories') : null,
        );
    }

    /**
     * Convert DTO to array, filtering out null values.
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'password' => $this->password,
            'gender' => $this->gender,
            'country_id' => $this->country_id,
            'categories' => $this->categories,
        ], fn($value) => $value !== null);
    }
}
