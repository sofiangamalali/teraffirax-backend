<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'vendor'       => new VendorResource($this->whenLoaded('vendor')),
            'name'         => $this->name,
            'price_before' => $this->price_before !== null ? (float) $this->price_before : null,
            'price_after'  => (float) $this->price_after,
            'discount'     => $this->discount,
            'product_url'  => $this->product_url,
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
