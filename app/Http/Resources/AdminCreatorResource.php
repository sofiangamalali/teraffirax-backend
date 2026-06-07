<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCreatorResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'pending_balance' => $this->pending_balance,
            'available_balance' => $this->available_balance,
            'withdrawn_balance' => $this->withdrawn_balance,
            'is_active' => $this->is_active,
            'is_email_verified' => $this->isEmailVerified(),
            'country' => new CountryResource($this->whenLoaded('country')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at,
        ];
    }
}
