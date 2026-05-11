<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by Policy
    }

    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:integer,float,boolean,json,string'],
            'group' => ['nullable', 'string', 'max:255'],
        ];
    }
}
