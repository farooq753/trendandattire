<?php

namespace App\Services;

use App\Support\Money;
use InvalidArgumentException;

class MeasurementNormalizer
{
    /**
     * Preserve the entered value and return the centimeter equivalent.
     *
     * @return array{original_value: string, original_unit: string, normalized_cm: string}
     */
    public function normalize(string $value, string $unit): array
    {
        $unit = strtolower($unit);

        if (! in_array($unit, ['cm', 'in'], true)) {
            throw new InvalidArgumentException('Measurements must use cm or in.');
        }

        $original = Money::round($value, 2);
        $centimeters = $unit === 'in'
            ? Money::round(Money::mul($original, '2.54'), 2)
            : $original;

        return [
            'original_value' => $original,
            'original_unit' => $unit,
            'normalized_cm' => $centimeters,
        ];
    }
}
