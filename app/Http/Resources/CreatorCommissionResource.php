<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\FormatsDatesWithTimezone;

class CreatorCommissionResource extends JsonResource
{
    use FormatsDatesWithTimezone;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'date' => $this->formatWithTimezone($this->date, $request),
            'clicks_count' => $this->clicks_count,
            'items_count' => $this->items_count,
            'returned_count' => $this->returned_count,
            'revenue_amount' => $this->revenue_amount,
            'commission_amount' => $this->commission_amount,
            'created_at' => $this->formatWithTimezone($this->created_at, $request),
            'currency' => $this->currency,
        ];
    }
}
