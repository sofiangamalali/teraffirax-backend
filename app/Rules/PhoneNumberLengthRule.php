<?php

namespace App\Rules;

use App\Models\Country;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumberLengthRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        protected
        ?int $countryId
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->countryId) {
            return;
        }

        $country = Country::find($this->countryId);

        if (!$country) {
            $fail(__('The selected country is invalid.'));
            return;
        }

        $length = strlen($value);

        if ($length < $country->phone_min_length || $length > $country->phone_max_length) {
            if ($country->phone_min_length === $country->phone_max_length) {
                $fail(__('The :attribute must be exactly :length digits.', ['length' => $country->phone_min_length]));
            } else {
                $fail(__('The :attribute must be between :min and :max digits.', ['min' => $country->phone_min_length, 'max' => $country->phone_max_length]));
            }
        }
    }
}
