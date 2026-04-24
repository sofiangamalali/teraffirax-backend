<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Withdrawal;

class ValidWithdrawalStatusTransition implements ValidationRule
{
    public function __construct(protected int $withdrawalId)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $withdrawal = Withdrawal::find($this->withdrawalId);

        if ($withdrawal && $withdrawal->status === 'done' && $value === 'pending') {
            $fail('Cannot change withdrawal status back to pending once it is done.');
        }
    }
}
