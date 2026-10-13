<?php

namespace App\Services;

use App\Models\ShippingMethod;
use App\Support\Money;
use Illuminate\Support\Collection;

class ShippingService
{
    /**
     * @return Collection<int, array{method: ShippingMethod, amount: string}>
     */
    public function quotes(string $country, ?string $region, string $subtotal, int $weightGrams): Collection
    {
        $methods = ShippingMethod::query()
            ->with('zone')
            ->where('is_active', true)
            ->whereHas('zone', function ($query) use ($country, $region) {
                $query->where('is_active', true)->whereHas('locations', function ($locations) use ($country, $region) {
                    $locations->where('country_code', $country)
                        ->where(function ($match) use ($region) {
                            $match->whereNull('region');
                            if ($region) {
                                $match->orWhere('region', $region);
                            }
                        });
                });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $methods->map(function (ShippingMethod $method) use ($subtotal, $weightGrams) {
            return [
                'method' => $method,
                'amount' => $this->amount($method, $subtotal, $weightGrams),
            ];
        })->filter(function (array $quote) use ($subtotal, $weightGrams) {
            $method = $quote['method'];

            if ($method->min_order !== null && Money::compare($subtotal, (string) $method->min_order) < 0) {
                return false;
            }

            if ($method->max_order !== null && Money::compare($subtotal, (string) $method->max_order) > 0) {
                return false;
            }

            if ($method->min_weight_grams !== null && $weightGrams < $method->min_weight_grams) {
                return false;
            }

            if ($method->max_weight_grams !== null && $weightGrams > $method->max_weight_grams) {
                return false;
            }

            return true;
        })->values();
    }

    public function amount(ShippingMethod $method, string $subtotal, int $weightGrams): string
    {
        if ($method->free_threshold !== null && Money::compare($subtotal, (string) $method->free_threshold) >= 0) {
            return '0.0000';
        }

        $amount = match ($method->rate_type) {
            'weight' => Money::add((string) $method->amount, Money::mul((string) $method->per_kg, Money::div((string) $weightGrams, '1000', 8))),
            'order_value' => Money::mul($subtotal, Money::div((string) $method->amount, '100', 8)),
            default => (string) $method->amount,
        };

        return Money::round($amount, 4);
    }
}
