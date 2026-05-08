<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ContentTemplateDTO
{
    public function __construct(
        public readonly int|string|null $creator_id = null,
        public readonly int|string|null $social_integration_id = null,
        public readonly ?string $name = null,
        public readonly ?string $content = null,
        public readonly ?bool $is_default = null,
    ) {
    }

    /**
     * Create a DTO from a Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            creator_id: $request->user()?->id,
            social_integration_id: $request->input('social_integration_id'),
            name: $request->input('name'),
            content: $request->input('content'),
            is_default: $request->input('is_default'),
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'creator_id' => $this->creator_id,
            'social_integration_id' => $this->social_integration_id,
            'name' => $this->name,
            'content' => $this->content,
            'is_default' => $this->is_default,
        ], fn($value) => $value !== null);
    }
}
