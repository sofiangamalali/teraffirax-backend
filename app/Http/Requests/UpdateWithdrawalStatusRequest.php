<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Withdrawal;
use App\Rules\ValidWithdrawalStatusTransition;

class UpdateWithdrawalStatusRequest extends FormRequest
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
            'status' => [
                'required',
                'in:pending,done',
                new ValidWithdrawalStatusTransition((int)$this->route('id'))
            ],
        ];
    }
}
