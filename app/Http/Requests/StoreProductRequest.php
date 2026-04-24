<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id'    => ['required', 'exists:vendors,id'],
            'name'         => ['required', 'string', 'max:255'],
            'price_before' => ['nullable', 'numeric', 'min:0'],
            'price_after'  => ['required', 'numeric', 'min:0'],
            'discount'     => ['nullable', 'string', 'max:255'],
            'product_url'  => ['required', 'url', 'max:500'],
        ];
    }
}
