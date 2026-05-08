<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Traits\FormatsDatesWithTimezone;

class TrackingLinkResource extends JsonResource
{
    use FormatsDatesWithTimezone;

    public function toArray(Request $request): array
    {
        return [
            'short_url' => url('/' . $this->slug),
            'clicks_count' => (int) $this->clicks_count,
            'created_at' => $this->formatWithTimezone($this->created_at, $request),
        ];
    }
}
