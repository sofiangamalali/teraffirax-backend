<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawMethodRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,wallet,other',
            'logo' => 'nullable|string',
            'details' => 'required|array',
            'is_active' => 'boolean'
        ];
    }
}
