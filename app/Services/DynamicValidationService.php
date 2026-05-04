<?php

namespace App\Services;

use App\Models\WithdrawMethodField;
use Illuminate\Support\Collection;

class DynamicValidationService
{
    /**
     * Convert JSON validation structures from fields into Laravel validation rules.
     *
     * @param Collection|array $fields
     * @return array
     */
    public function convertToLaravelRules($fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];
            $validation = $field instanceof WithdrawMethodField ? $field->validation : ($field['validation'] ?? []);

            if (empty($validation)) {
                $rules[$field['name'] ?? $field->name] = ['nullable'];
                continue;
            }

            // Required check
            if (isset($validation['required']) && $validation['required']) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type check
            if (isset($validation['type'])) {
                $type = $validation['type'];
                if ($type === 'number') {
                    $fieldRules[] = 'numeric';
                } elseif ($type === 'email') {
                    $fieldRules[] = 'email';
                } else {
                    $fieldRules[] = 'string';
                }
            }

            // Digits check (fixed length for numbers)
            if (isset($validation['digits'])) {
                $fieldRules[] = 'digits:' . $validation['digits'];
            }

            // Min/Max checks
            if (isset($validation['min'])) {
                $fieldRules[] = 'min:' . $validation['min'];
            }
            if (isset($validation['max'])) {
                $fieldRules[] = 'max:' . $validation['max'];
            }

            // Custom Regex
            if (isset($validation['regex'])) {
                $pattern = $validation['regex'];
                if (!str_starts_with($pattern, '/') && !str_starts_with($pattern, '#')) {
                    $pattern = '/' . $pattern . '/';
                }
                $fieldRules[] = 'regex:' . $pattern;
            }

            $rules[$field instanceof WithdrawMethodField ? $field->name : $field['name']] = $fieldRules;
        }

        return $rules;
    }
}
