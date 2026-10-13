<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use App\Support\Money;

class CouponService
{
    public function __construct(private CurrencyService $currencies) {}

    /**
     * @return array{ok: bool, error: ?string, discount: string, code: ?string}
     */
    public function discount(string $code, string $subtotal, string $currency, ?User $user, ?string $email): array
    {
        $coupon = Coupon::query()->whereRaw('upper(code) = ?', [strtoupper(trim($code))])->first();

        if (! $coupon || ! $coupon->is_active) {
            return $this->fail('That coupon is not active.');
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return $this->fail('That coupon is not active yet.');
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            return $this->fail('That coupon has expired.');
        }

        if ($coupon->min_subtotal !== null && Money::compare($subtotal, (string) $coupon->min_subtotal) < 0) {
            return $this->fail('The order has not reached this coupon minimum.');
        }

        if ($coupon->usage_limit !== null && $coupon->usages()->count() >= $coupon->usage_limit) {
            return $this->fail('That coupon has reached its usage limit.');
        }

        $identity = $user?->id;
        $mail = strtolower($email ?? $user?->email ?? '');

        if ($coupon->per_customer_limit !== null && $mail !== '') {
            $used = $coupon->usages()
                ->where(function ($query) use ($identity, $mail) {
                    $query->where('email', $mail);
                    if ($identity) {
                        $query->orWhere('user_id', $identity);
                    }
                })
                ->count();

            if ($used >= $coupon->per_customer_limit) {
                return $this->fail('That coupon has already been used on this account.');
            }
        }

        if ($coupon->type === 'percent') {
            $discount = Money::round(Money::div(Money::mul($subtotal, (string) $coupon->value), '100', 8), 4);
        } else {
            try {
                $converted = $this->currencies->convert(
                    (string) $coupon->value,
                    $coupon->currency_code ?: $this->currencies->defaultCode(),
                    $currency,
                );
            } catch (\RuntimeException) {
                return $this->fail('That coupon cannot be converted into the checkout currency.');
            }

            $discount = $converted['amount'];
        }

        if ($coupon->max_discount !== null) {
            $cap = $this->currencies->convert(
                (string) $coupon->max_discount,
                $coupon->currency_code ?: $this->currencies->defaultCode(),
                $currency,
            )['amount'];
            $discount = Money::min($discount, $cap);
        }

        $discount = Money::min($discount, $subtotal);

        return [
            'ok' => true,
            'error' => null,
            'discount' => Money::round($discount, 4),
            'code' => $coupon->code,
        ];
    }

    /**
     * @return array{ok: bool, error: string, discount: string, code: null}
     */
    private function fail(string $error): array
    {
        return ['ok' => false, 'error' => $error, 'discount' => '0.0000', 'code' => null];
    }
}
