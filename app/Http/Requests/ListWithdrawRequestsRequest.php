<?php

namespace App\Http\Requests;

use App\Enums\WithdrawRequestStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListWithdrawRequestsRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::enum(WithdrawRequestStatusEnum::class)],
            'creator_withdraw_method_id' => [
                'nullable',
                'integer',
                Rule::exists('creator_withdraw_methods', 'id')->where(function ($query) {
                    $query->where('creator_id', $this->user()->id);
                }),
            ],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }
}
