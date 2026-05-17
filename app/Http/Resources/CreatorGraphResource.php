<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreatorGraphResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'graph' => array_map(function ($item) {
                return [
                    'date' => $item['date'],
                    'commission' => (double) $item['commission'],
                    'clicks' => (int) $item['clicks'],
                    'orders' => (int) $item['orders'],
                    'currency' => $item['currency'],
                ];
            }, $this['graph']),
            'summary' => [
                'clicks' => (int) $this['summary']['clicks'],
                'orders' => (int) $this['summary']['orders'],
                'conversion_rate' => (double) $this['summary']['conversion_rate'],
                'shipped_items' => (int) $this['summary']['shipped_items'],
                'returned_items' => (int) $this['summary']['returned_items'],
                'commission' => (double) $this['summary']['commission'],
                'currency' => $this['summary']['currency'],
            ],
        ];
    }
}
