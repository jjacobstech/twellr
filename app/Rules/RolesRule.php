<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\ValidationRule;

class RolesRule implements ValidationRule
{
    protected $email;

    public function __construct($userEmail)
    {
        $this->email = $userEmail;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userRole = User::where('email', '=', $this->email)->value('role');

        if ($userRole != $value) {
            $fail('There is no ' . $value . ' with these credentials');
        }
    }
}