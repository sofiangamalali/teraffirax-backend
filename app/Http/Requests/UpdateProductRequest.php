<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id'    => ['sometimes', 'required', 'exists:vendors,id'],
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'price_before' => ['nullable', 'numeric', 'min:0'],
            'price_after'  => ['sometimes', 'required', 'numeric', 'min:0'],
            'discount'     => ['nullable', 'string', 'max:255'],
            'product_url'  => ['sometimes', 'required', 'url', 'max:500'],
        ];
    }
}
