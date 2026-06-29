<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'vendor_id' => $this->vendor_id,
            'vendor_category_id' => $this->id,
            'category_id' => $this->category_id,
            'external_id' => $this->external_id,
        ];
    }
}
