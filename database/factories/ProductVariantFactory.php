<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'VAR-'.fake()->unique()->numerify('######'),
            'combination_key' => 'size:m|color:navy-'.fake()->unique()->numerify('###'),
            'stock_on_hand' => 5,
            'reserved' => 0,
            'is_available' => true,
            'weight_grams' => 350,
        ];
    }
}
