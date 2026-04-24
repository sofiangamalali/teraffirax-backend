<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'slug'         => $this->slug,
            'short_url'    => url('/' . $this->slug),
            'redirect_url' => $this->redirect_url,
            'clicks_count' => (int) $this->clicks_count,
            'created_at'   => $this->created_at?->toDateTimeString(),
        ];
    }
}
