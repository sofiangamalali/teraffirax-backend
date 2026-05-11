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
            'gender' => $this->gender ? __('messages.' . $this->gender) : null,
            'is_active' => $this->isActive(),
            'is_email_verified' => $this->isEmailVerified(),
            'wallet' => [
                'pending_balance' => $this->pending_balance,
                'available_balance' => $this->available_balance,
                'withdrawn_balance' => $this->withdrawn_balance,
                'currency' => $this->country->currency,
                'min_withdrawal_amount' => setting('withdrawal.min_amount'),
            ],
            'country' => new CountryResource($this->whenLoaded('country')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'account_manager' => new AccountManagerResource($this->whenLoaded('accountManager')),
        ];
    }
}
