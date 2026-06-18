<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'last_posted_at' => $this->whenPivotLoaded('category_creator', function () {
                return $this->pivot->last_posted_at;
            }),
        ];
    }
}
