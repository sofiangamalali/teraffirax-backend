<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSocialIntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'creator_id' => $this->creator_id,
            'platform' => $this->platform,
            'platform_logo_url' => $this->platform_logo_url,
            'type' => $this->type,
            'status' => $this->status,
            'external_id' => $this->external_id,
            'name' => $this->name,
            'username' => $this->username,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
