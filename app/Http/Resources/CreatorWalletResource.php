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
            'pending_balance' => number_format($this['pending_balance'], 2, '.', ''),
            'available_balance' => number_format($this['available_balance'], 2, '.', ''),
            'withdrawn_balance' => number_format($this['withdrawn_balance'], 2, '.', ''),
            'currency' => $this['currency'],
        ];
    }
}
