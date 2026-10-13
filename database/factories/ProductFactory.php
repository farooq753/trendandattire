<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = 'Scrub '.fake()->unique()->word();

        return [
            'name' => $name,
            'sku' => 'TA-'.fake()->unique()->numerify('######'),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'short_description' => 'Sample catalog copy for local development.',
            'description' => 'A development catalog row. It does not state a fabric certification or country of origin.',
            'gender' => 'unisex',
            'garment_type' => 'set',
            'fulfillment_type' => 'ready',
            'regular_price' => '68.0000',
            'currency_code' => 'USD',
            'is_published' => true,
            'published_at' => now(),
            'track_inventory' => true,
            'low_stock_threshold' => 2,
        ];
    }
}
