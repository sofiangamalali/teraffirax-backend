<?php

namespace App\Http\Requests\Auth;

use App\Rules\PhoneNumberLengthRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'integer', 'exists:categories,id', 'distinct'],
            'email' => ['required', 'email', 'unique:creators,email', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
