<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawMethodResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo_url,
            'is_active' => $this->is_active,
            'country' => new CountryResource($this->whenLoaded('country')),
            'fields' => WithdrawMethodFieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}
