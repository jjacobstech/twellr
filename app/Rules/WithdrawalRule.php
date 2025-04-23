<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\ValidationRule;

class WithdrawalRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $wallet_balance = User::where('id', '=', Auth::id())->value('wallet_balance');
        if ($wallet_balance < $value) {
            $fail("$value is higher than your current balance");
        }
    }
}
