<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'iso2' => $this->iso2,
            'phone_code' => $this->phone_code,
            'phone_min_length' => $this->phone_min_length,
            'phone_max_length' => $this->phone_max_length,
            'icon' => $this->icon,
        ];
    }
}
