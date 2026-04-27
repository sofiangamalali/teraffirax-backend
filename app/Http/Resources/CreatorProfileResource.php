<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorProfileResource extends JsonResource
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
            'is_active' => $this->isActive(),
            'is_verified' => $this->isEmailVerified(),
            'country' => new CountryResource($this->whenLoaded('country')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'account_manager' => new AccountManagerResource($this->whenLoaded('accountManager')),

        ];
    }
}
