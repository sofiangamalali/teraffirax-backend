<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class SettingDTO
{
    public function __construct(
        public readonly string $key,
        public readonly ?string $value,
        public readonly ?string $type = 'string',
        public readonly ?string $group = 'general',
        public readonly ?int $user_id = null,
    ) {
    }

    /**
     * Create a DTO from a Form Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            key: $request->input('key'),
            value: $request->input('value'),
            type: $request->input('type', 'string'),
            group: $request->input('group', 'general'),
            user_id: $request->user()?->id,
        );
    }

    /**
     * Convert DTO to array, filtering out null values except value which can be null.
     */
    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'group' => $this->group,
        ], fn($value, $key) => $value !== null || $key === 'value', ARRAY_FILTER_USE_BOTH);
    }
}
