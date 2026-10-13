<?php

namespace Database\Factories;

use App\Models\TaxRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxRule>
 */
class TaxRuleFactory extends Factory
{
    protected $model = TaxRule::class;

    public function definition(): array
    {
        return [
            'country_code' => 'US',
            'region' => 'CA',
            'name' => 'Sample regional rate',
            'rate_percent' => '7.2500',
            'price_mode' => 'exclusive',
            'applies_to' => 'products',
            'is_active' => true,
            'priority' => 10,
        ];
    }
}
