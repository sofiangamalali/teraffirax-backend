<?php

namespace App\Rules;

use App\Models\WithdrawMethod;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckWithdrawMethodCountry implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $method = WithdrawMethod::find($value);

        if ($method && $method->country_id && $method->country_id !== request()->user()->country_id) {
            $fail(__('This withdrawal method is not available in your country.'));
        }
    }
}
