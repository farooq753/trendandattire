<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::query()->create([
                'name' => 'Domestic',
                'slug' => 'domestic-'.fake()->unique()->numerify('###'),
                'is_international' => false,
                'is_active' => true,
            ])->id,
            'name' => 'Ground',
            'code' => 'ground',
            'rate_type' => 'flat',
            'amount' => '8.0000',
            'allows_cod' => true,
            'is_active' => true,
        ];
    }
}
