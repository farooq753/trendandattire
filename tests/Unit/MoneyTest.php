<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_uses_decimal_rounding(): void
    {
        $this->assertSame('10.13', Money::round(Money::mul('10.125', '1'), 2));
        $this->assertSame('2.50', Money::round(Money::div('5', '2'), 2));
        $this->assertSame(0, Money::compare('10.0000', '10'));
    }
}
