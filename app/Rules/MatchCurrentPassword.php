<?php

namespace App\Rules;

use App\Helpers\Helpers;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class MatchCurrentPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Helpers::isUserLogin()) {
            $fail('Unauthenticated.');
        }

        if (! Hash::check($value, auth()->user()->password)) {
            $fail(__('passwords.passowrd_does_not_match'));
        }
    }
}
