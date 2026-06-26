<?php

namespace App\DTOs;

use App\Http\Requests\Admin\UpdateAdminCreatorVendorTrackingRequest;

class AdminCreatorVendorTrackingDTO
{
    public function __construct(
        public int $creator_id,
        public string $track_id,
        public bool $is_active,
    ) {}

    public static function fromRequest(UpdateAdminCreatorVendorTrackingRequest $request, int $creatorId): self
    {
        return new self(
            creator_id: $creatorId,
            track_id: (string) ($request->input('track_id') ?? ''),
            is_active: (bool) $request->input('is_active'),
        );
    }

    public function toArray(): array
    {
        return [
            'creator_id' => $this->creator_id,
            'track_id' => $this->track_id,
            'is_active' => $this->is_active,
        ];
    }
}
