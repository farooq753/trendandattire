<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case BankTransfer = 'bank_transfer';
    case Stripe = 'stripe';
    case Paypal = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Cash on delivery',
            self::BankTransfer => 'Bank transfer',
            self::Stripe => 'Card (Stripe)',
            self::Paypal => 'PayPal',
        };
    }
}
