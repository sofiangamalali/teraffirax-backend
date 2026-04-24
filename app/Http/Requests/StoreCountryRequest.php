<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100'],
            'iso2'             => ['required', 'string', 'size:2', 'unique:countries,iso2'],
            'phone_code'       => ['required', 'string', 'max:10'],
            'phone_min_length' => ['required', 'integer', 'min:1', 'max:127'],
            'phone_max_length' => ['required', 'integer', 'min:1', 'max:127', 'gte:phone_min_length'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }
}
