<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'short_url' => url('/' . $this->slug),
            'clicks_count' => (int) $this->clicks_count,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
