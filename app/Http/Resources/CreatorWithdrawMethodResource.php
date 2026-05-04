<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorWithdrawMethodResource extends JsonResource
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
            'is_default' => $this->is_default,
            'method' => new WithdrawMethodResource($this->whenLoaded('withdrawMethod')),
            'values' => $this->values->pluck('field_value', 'field_name'),
        ];
    }
}
