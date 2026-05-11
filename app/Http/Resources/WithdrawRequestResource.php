<?php

namespace App\Http\Resources;

use App\Traits\FormatsDatesWithTimezone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawRequestResource extends JsonResource
{
    use FormatsDatesWithTimezone;

    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->amount,
            'status' => __('messages.' . $this->status->value),
            'creator_withdraw_method' => new CreatorWithdrawMethodResource($this->withdrawMethod),
            'created_at' => $this->formatWithTimezone($this->created_at, $request),
            'updated_at' => $this->formatWithTimezone($this->updated_at, $request),
        ];
    }
}
