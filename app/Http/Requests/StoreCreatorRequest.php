<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PhoneNumberLengthRule;

class StoreCreatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'phone' => [
                'required',
                'string',
                'unique:creators,phone',
                new PhoneNumberLengthRule($this->country_id)
            ],
            'email' => ['required', 'email', 'unique:creators,email', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
