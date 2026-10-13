<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Support\Money;
use Illuminate\Support\Carbon;
use RuntimeException;

class CurrencyService
{
    public function defaultCode(): string
    {
        return Currency::query()->where('is_default', true)->value('code')
            ?? Currency::query()->where('is_active', true)->value('code')
            ?? 'USD';
    }

    public function find(string $code): ?Currency
    {
        return Currency::query()->where('code', $code)->where('is_active', true)->first();
    }

    /**
     * Convert an amount using the latest manual rate. Same-currency conversion uses rate 1.
     *
     * @return array{amount: string, rate: string, quoted_at: Carbon, base: string, quote: string}
     */
    public function convert(string $amount, string $from, string $to): array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            $currency = $this->find($to) ?? new Currency(['decimal_places' => 2]);

            return [
                'amount' => Money::round($amount, (int) $currency->decimal_places),
                'rate' => '1.00000000',
                'quoted_at' => now(),
                'base' => $from,
                'quote' => $to,
            ];
        }

        $rate = ExchangeRate::query()
            ->where('base_code', $from)
            ->where('quote_code', $to)
            ->where('effective_at', '<=', now())
            ->orderByDesc('effective_at')
            ->orderByDesc('id')
            ->first();

        if (! $rate) {
            throw new RuntimeException("No saved exchange rate is configured from {$from} to {$to}.");
        }

        $quote = $this->find($to);
        $decimals = (int) ($quote->decimal_places ?? 2);
        $converted = Money::round(Money::mul($amount, (string) $rate->rate), $decimals);

        return [
            'amount' => $converted,
            'rate' => (string) $rate->rate,
            'quoted_at' => $rate->effective_at,
            'base' => $from,
            'quote' => $to,
        ];
    }

    public function canCheckoutIn(string $currency): bool
    {
        $currency = strtoupper($currency);

        if (! $this->find($currency)) {
            return false;
        }

        if ($currency === $this->defaultCode()) {
            return true;
        }

        return ExchangeRate::query()
            ->where('base_code', $this->defaultCode())
            ->where('quote_code', $currency)
            ->where('effective_at', '<=', now())
            ->exists();
    }

    public function format(string $amount, ?string $currencyCode = null): string
    {
        $code = $currencyCode ?? $this->defaultCode();
        $currency = Currency::query()->find($code);

        return Money::format($amount, $currency->symbol ?? $code.' ', (int) ($currency->decimal_places ?? 2));
    }
}
