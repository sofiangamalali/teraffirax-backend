<?php

namespace App\Support;

class TemplateVariableRegistry
{
    protected array $variables = [
        'telegram' => [
            [
                'key' => 'discount',
                'label' => 'Discount Percentage',
                'example' => '40'
            ],
            [
                'key' => 'product_name',
                'label' => 'Product Name',
                'example' => 'iPhone 15'
            ],
            [
                'key' => 'product_price',
                'label' => 'Product Price',
                'example' => '999'
            ],
            [
                'key' => 'link',
                'label' => 'Affiliate Link',
                'example' => 'https://t.ly/abc'
            ],
        ]
    ];

    public function get(string $platform): array
    {
        return $this->variables[$platform] ?? [];
    }

    public function getAllowedKeys(string $platform): array
    {
        return array_column($this->get($platform), 'key');
    }
}
