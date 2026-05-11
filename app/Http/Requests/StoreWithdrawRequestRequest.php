<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWithdrawRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'creator_withdraw_method_id' => [
                'required',
                'integer',
                Rule::exists('creator_withdraw_methods', 'id')->where(function ($query) {
                    $query->where('creator_id', $this->user()->id);
                }),
            ],
        ];
    }
}
