<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorWalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pending_balance' => $this['pending_balance'],
            'available_balance' => $this['available_balance'],
            'withdrawn_balance' => $this['withdrawn_balance'],
            'currency' => $this['currency'],
            'min_withdrawal_amount' => $this['min_withdrawal_amount'],
        ];
    }
}
