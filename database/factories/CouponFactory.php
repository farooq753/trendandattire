<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => '10.0000',
            'currency_code' => 'USD',
            'is_active' => true,
        ];
    }
}
