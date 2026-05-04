<?php

namespace App\Http\Requests;

use App\Models\WithdrawMethod;
use App\Rules\CheckWithdrawMethodCountry;
use App\Services\DynamicValidationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreatorWithdrawMethodRequest extends FormRequest
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
        $rules = [
            'withdraw_method_id' => [
                'required',
                'exists:withdraw_methods,id',
                new CheckWithdrawMethodCountry(),
            ],
            'is_default' => 'boolean',
            'values' => 'required|array'
        ];

        if ($this->has('withdraw_method_id')) {
            $method = WithdrawMethod::with('fields')->find($this->withdraw_method_id);
            if ($method) {
                $dynamicRules = app(DynamicValidationService::class)->convertToLaravelRules($method->fields);
                foreach ($dynamicRules as $field => $fieldRules) {
                    $rules["values.{$field}"] = $fieldRules;
                }
            }
        }

        return $rules;
    }
}
