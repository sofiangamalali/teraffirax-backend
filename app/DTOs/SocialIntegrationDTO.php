<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class SocialIntegrationDTO
{
    public function __construct(
        public readonly int|string|null $creator_id = null,
        public readonly ?string $platform = null,
        public readonly ?string $type = null,
        public readonly ?string $status = null,
        public readonly ?string $external_id = null,
        public readonly ?string $name = null,
        public readonly ?string $username = null,
        public readonly ?string $access_token = null,
        public readonly ?array $metadata = null,
    ) {
    }

    /**
     * Create a DTO from a Request.
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            creator_id: $request->user()?->id,
            platform: $request->input('platform'),
            type: $request->input('type'),
            status: $request->input('status', 'pending'),
            external_id: $request->input('external_id'),
            name: $request->input('name'),
            username: $request->input('username'),
            access_token: $request->input('access_token'),
            metadata: $request->input('metadata'),
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'creator_id' => $this->creator_id,
            'platform' => $this->platform,
            'type' => $this->type,
            'status' => $this->status,
            'external_id' => $this->external_id,
            'name' => $this->name,
            'username' => $this->username,
            'access_token' => $this->access_token,
            'metadata' => $this->metadata,
        ], fn($value) => $value !== null);
    }
}
