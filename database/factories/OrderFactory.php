<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'number' => 'TA-'.now()->format('Y').'-'.fake()->unique()->numerify('######'),
            'tracking_token' => bin2hex(random_bytes(32)),
            'checkout_token' => Str::random(40),
            'currency_code' => 'USD',
            'fx_base_code' => 'USD',
            'fx_rate' => '1.00000000',
            'fx_quoted_at' => now(),
            'subtotal' => '59.0000',
            'discount_total' => '0.0000',
            'shipping_total' => '8.0000',
            'tax_total' => '0.0000',
            'cod_fee' => '0.0000',
            'grand_total' => '67.0000',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => '+12025550123',
            'shipping_method_name' => 'Ground',
            'placed_at' => now(),
        ];
    }
}
