<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCidr implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !$this->isValidCidr($value)) {
            $fail(__('The :attribute must be a valid CIDR or IP address.'), ['attribute' => $attribute]);
        }
    }

    private function isValidCidr(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        if (!str_contains($value, '/')) {
            return filter_var($value, FILTER_VALIDATE_IP) !== false;
        }

        [$ip, $prefix] = explode('/', $value, 2);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $prefixLength = (int) $prefix;

            return $prefixLength >= 0 && $prefixLength <= 32;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $prefixLength = (int) $prefix;

            return $prefixLength >= 1 && $prefixLength <= 128;
        }

        return false;
    }
}
