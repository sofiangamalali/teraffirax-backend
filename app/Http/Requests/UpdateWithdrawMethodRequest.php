<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWithdrawMethodRequest extends FormRequest
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
            'country_id' => 'sometimes|required|exists:countries,id',
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:bank,wallet,other',
            'logo' => 'sometimes|nullable|string',
            'details' => 'sometimes|required|array',
            'is_active' => 'sometimes|boolean'
        ];
    }
}
