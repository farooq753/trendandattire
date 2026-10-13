<?php

namespace App\Services;

use App\Models\TaxRule;
use App\Support\Money;

class TaxService
{
    /**
     * Apply the most specific active rule for products and, separately, shipping.
     * Rates are not a single global percentage.
     *
     * @return array{total: string, inclusive: bool, lines: array<int, array<string, string>>}
     */
    public function calculate(string $country, ?string $region, string $productAmount, string $shippingAmount): array
    {
        $rules = TaxRule::query()
            ->where('country_code', $country)
            ->where('is_active', true)
            ->where(function ($query) use ($region) {
                $query->whereNull('region');
                if ($region) {
                    $query->orWhere('region', $region);
                }
            })
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get()
            ->sortByDesc(fn (TaxRule $rule) => ($region && $rule->region === $region ? 1000 : 0) + $rule->priority)
            ->values();

        $productRule = $rules->first(fn (TaxRule $rule) => in_array($rule->applies_to, ['products', 'both'], true));
        $shippingRule = $rules->first(fn (TaxRule $rule) => in_array($rule->applies_to, ['shipping', 'both'], true));

        $lines = [];
        $total = '0.0000';
        $inclusive = false;

        if ($productRule) {
            $line = $this->line($productRule, $productAmount, 'products');
            $lines[] = $line;
            $inclusive = $productRule->price_mode === 'inclusive';
            if ($productRule->price_mode === 'exclusive') {
                $total = Money::add($total, $line['amount']);
            }
        }

        if ($shippingRule && Money::compare($shippingAmount, '0') > 0) {
            $line = $this->line($shippingRule, $shippingAmount, 'shipping');
            $lines[] = $line;
            if ($shippingRule->price_mode === 'exclusive') {
                $total = Money::add($total, $line['amount']);
            }
        }

        return [
            'total' => Money::round($total, 4),
            'inclusive' => $inclusive,
            'lines' => $lines,
        ];
    }

    /**
     * @return array{name: string, amount: string, rate: string, applies_to: string, price_mode: string}
     */
    private function line(TaxRule $rule, string $base, string $appliesTo): array
    {
        $rate = Money::div((string) $rule->rate_percent, '100', 8);

        $amount = $rule->price_mode === 'inclusive'
            ? Money::sub($base, Money::div($base, Money::add('1', $rate), 8))
            : Money::mul($base, $rate);

        return [
            'name' => $rule->name,
            'amount' => Money::round($amount, 4),
            'rate' => (string) $rule->rate_percent,
            'applies_to' => $appliesTo,
            'price_mode' => $rule->price_mode,
        ];
    }
}
