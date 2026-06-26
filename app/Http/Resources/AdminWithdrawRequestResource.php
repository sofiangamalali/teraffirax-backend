<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminWithdrawRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'creator_id' => $this->creator_id,
            'creator' => new AdminCreatorResource($this->whenLoaded('creator')),
            'creator_withdraw_method_id' => $this->creator_withdraw_method_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'requested_at' => $this->requested_at,
            'reviewed_at' => $this->reviewed_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
