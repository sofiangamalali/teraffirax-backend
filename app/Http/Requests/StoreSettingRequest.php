<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by Policy
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', 'unique:settings,key'],
            'value' => ['nullable', 'string'],
            'type' => ['required', 'string', 'in:integer,float,boolean,json,string'],
            'group' => ['nullable', 'string', 'max:255'],
        ];
    }
}
