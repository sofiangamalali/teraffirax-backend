<?php

namespace App\Http\Requests\Scraper;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpsertProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'vendor_category_id' => ['required', 'integer', 'exists:vendor_categories,id'],
            'products' => ['required', 'array', 'min:1', 'max:100'],
            'products.*.external_id' => ['required', 'string', 'max:255'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.url' => ['required', 'string'],
            'products.*.price_before' => ['nullable', 'numeric', 'min:0'],
            'products.*.price_after' => ['required', 'numeric', 'min:0'],
            'products.*.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'products.*.rank_in_vendor_category' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
