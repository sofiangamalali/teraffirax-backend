<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListCreatorCommissionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
