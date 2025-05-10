<?php

namespace App\Rules;

use App\Models\AdminSetting;
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
        $wallet_balance = User::where('id', '=', Auth::id())->value(column: 'wallet_balance');
        $withdrawal_threshold = AdminSetting::first()->value('withdrawal_threshold');

        if ($wallet_balance < $value) {
            $fail("Insufficient balance");
        } elseif ($withdrawal_threshold > $value) {
            $fail("Current withdrawal is below Withdrawal Threshold - $$withdrawal_threshold");
        }
    }
}
