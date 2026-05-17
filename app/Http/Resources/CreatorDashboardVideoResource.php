<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorDashboardVideoResource extends JsonResource
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
            'video_url' => $this->video_url,
            'thumbnail_url' => $this->thumbnail_url,
            'country' => $this->relationLoaded('country') && $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
                'currency' => $this->country->currency,
            ] : null,
            'views_count' => (int) $this->views_count,
            'likes_count' => (int) $this->likes_count,
            'comments_count' => (int) $this->comments_count,
            'shares_count' => (int) $this->shares_count,
            'total_orders' => (int) $this->total_orders,
            'total_commission' => (double) $this->total_commission,
        ];
    }
}
