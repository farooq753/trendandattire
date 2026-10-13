<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'code' => 'US',
            'name' => 'United States',
            'phone_prefix' => '+1',
            'is_active' => true,
            'address_requires_region' => true,
            'address_requires_postal' => true,
            'region_label' => 'State',
        ];
    }
}
