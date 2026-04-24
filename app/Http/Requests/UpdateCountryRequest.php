<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
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
        $countryId = $this->route('country') ? $this->route('country')->id : null;

        return [
            'name'             => ['sometimes', 'required', 'string', 'max:100'],
            'iso2'             => ['sometimes', 'required', 'string', 'size:2', 'unique:countries,iso2,' . $countryId],
            'phone_code'       => ['sometimes', 'required', 'string', 'max:10'],
            'phone_min_length' => ['sometimes', 'required', 'integer', 'min:1', 'max:127'],
            'phone_max_length' => ['sometimes', 'required', 'integer', 'min:1', 'max:127', 'gte:phone_min_length'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }
}
