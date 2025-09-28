<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LatitudeLongitude implements Rule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function passes($attribute, $value)
    {
        // Validate latitude and longitude format
        $pattern = '/^[-+]?([0-8]?\d(\.\d+)?|90(\.0+)?)$/';

        return preg_match($pattern, $value);
    }

    public function message()
    {
        return 'Invalid :attribute format.';
    }
}
