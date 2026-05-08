<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCreatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creator = $this->route('creator');
        $creatorId = is_object($creator) ? $creator->id : $creator;
        $activeCountryId = $this->input('country_id') ?? (is_object($creator) ? $creator->country_id : null);

        return [
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'country_id'      => ['sometimes', 'required', 'integer', 'exists:countries,id'],
            'phone'           => [
                'sometimes', 
                'required', 
                'string', 
                'unique:creators,phone,' . $creatorId, 
                new \App\Rules\PhoneNumberLengthRule($activeCountryId)
            ],
            'email'           => ['sometimes', 'required', 'email', 'unique:creators,email,' . $creatorId, 'max:255'],
            'password'        => ['sometimes', 'required', 'string', 'min:8'],
            'gender'          => ['sometimes', 'required', 'in:male,female'],
            'balance'         => ['sometimes', 'numeric'],
        ];
    }
}
