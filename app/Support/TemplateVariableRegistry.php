<?php

namespace App\Support;

class TemplateVariableRegistry
{
    protected array $variables = [
        'telegram' => [
            [
                'key' => 'product_name',
                'label' => 'Product Name',
                'example' => 'iPhone 15',
                'required' => true,
            ],
            [
                'key' => 'product_price',
                'label' => 'Product Price',
                'example' => '999',
                'required' => true,
            ],
            [
                'key' => 'link',
                'label' => 'Product Link',
                'example' => 'https://t.ly/abc',
                'required' => true,
            ],
        ]
    ];

    public function get(string $platform): array
    {
        $platformVariables = $this->variables[$platform] ?? [];
        foreach ($platformVariables as &$variable) {
            if (isset($variable['label'])) {
                $variable['label'] = __($variable['label']);
            }
        }
        return $platformVariables;
    }

    public function getAllowedKeys(string $platform): array
    {
        return array_column($this->get($platform), 'key');
    }

    public function getRequiredKeys(string $platform): array
    {
        $platformVariables = $this->get($platform);
        $required = array_filter($platformVariables, fn($var) => $var['required'] ?? false);
        return array_column($required, 'key');
    }

    public function getLabelsForKeys(string $platform, array $keys): array
    {
        $platformVariables = $this->get($platform);
        $labels = [];

        foreach ($keys as $key) {
            $variable = array_filter($platformVariables, fn($var) => $var['key'] === $key);
            $variable = reset($variable);
            $labels[] = $variable ? $variable['label'] : $key;
        }

        return $labels;
    }
}
