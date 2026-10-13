<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InternationalPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = preg_replace('/[^\d+]/', '', (string) $value) ?? '';

        if (! preg_match('/^\+?[1-9]\d{6,14}$/', $normalized)) {
            $fail('Enter a valid international phone number, including the country code.');
        }
    }
}
