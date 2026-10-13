<?php

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

class Money
{
    public static function of(string|int $amount): BigDecimal
    {
        $normalized = trim((string) $amount);

        if ($normalized === '' || ! preg_match('/^-?\d+(\.\d+)?$/', $normalized)) {
            throw new InvalidArgumentException('Money amounts must be decimal strings.');
        }

        return BigDecimal::of($normalized);
    }

    public static function normalize(string|int|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '0.0000';
        }

        return (string) self::of($amount);
    }

    public static function add(string|int $left, string|int $right): string
    {
        return (string) self::of($left)->plus(self::of($right));
    }

    public static function sub(string|int $left, string|int $right): string
    {
        return (string) self::of($left)->minus(self::of($right));
    }

    public static function mul(string|int $left, string|int $right): string
    {
        return (string) self::of($left)->multipliedBy(self::of($right));
    }

    public static function div(string|int $left, string|int $right, int $scale = 8): string
    {
        return (string) self::of($left)->dividedBy(self::of($right), $scale, RoundingMode::HalfUp);
    }

    public static function round(string|int $amount, int $scale): string
    {
        return (string) self::of($amount)->toScale($scale, RoundingMode::HalfUp);
    }

    public static function compare(string|int $left, string|int $right): int
    {
        return self::of($left)->compareTo(self::of($right));
    }

    public static function min(string|int $left, string|int $right): string
    {
        return self::compare($left, $right) <= 0 ? self::normalize($left) : self::normalize($right);
    }

    public static function isPositive(string|int $amount): bool
    {
        return self::of($amount)->isPositive();
    }

    public static function format(string|int $amount, string $symbol, int $decimals): string
    {
        $rounded = self::round($amount, $decimals);
        $negative = str_starts_with($rounded, '-');
        $rounded = ltrim($rounded, '-');
        [$whole, $fraction] = array_pad(explode('.', $rounded, 2), 2, '');
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole) ?? $whole;
        $body = $decimals > 0
            ? $whole.'.'.str_pad(substr($fraction, 0, $decimals), $decimals, '0')
            : $whole;

        return ($negative ? '-' : '').$symbol.$body;
    }
}
