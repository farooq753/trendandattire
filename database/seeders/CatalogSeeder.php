<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'tops' => ['Tops', 'top'],
            'pants' => ['Pants', 'pants'],
            'sets' => ['Sets', 'set'],
            'jackets' => ['Jackets and lab coats', 'jacket'],
            'accessories' => ['Accessories', 'accessory'],
        ];

        $categories = [];

        foreach ($groups as $slug => [$name]) {
            $categories[$slug] = Category::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'nav_group' => $slug,
                    'is_active' => true,
                    'sort_order' => array_search($slug, array_keys($groups), true),
                    'meta_title' => $name,
                    'meta_description' => $name.' in the Trend & Attire catalog.',
                ],
            );
        }

        $size = ProductAttribute::query()->updateOrCreate(
            ['slug' => 'size'],
            ['name' => 'Size', 'type' => 'size', 'is_filterable' => true, 'sort_order' => 1],
        );
        $color = ProductAttribute::query()->updateOrCreate(
            ['slug' => 'color'],
            ['name' => 'Color', 'type' => 'color', 'is_filterable' => true, 'sort_order' => 2],
        );

        $sizes = [];
        foreach (['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'] as $index => $label) {
            $sizes[$label] = AttributeValue::query()->updateOrCreate(
                ['product_attribute_id' => $size->id, 'slug' => strtolower($label)],
                ['value' => $label, 'sort_order' => $index],
            );
        }

        $colors = [];
        foreach (['Navy' => 'navy', 'Teal' => 'teal', 'Sand' => 'sand'] as $label => $slug) {
            $colors[$slug] = AttributeValue::query()->updateOrCreate(
                ['product_attribute_id' => $color->id, 'slug' => $slug],
                ['value' => $label, 'sort_order' => count($colors)],
            );
        }

        $set = Product::query()->updateOrCreate(
            ['sku' => 'TA-SET-CORE'],
            [
                'category_id' => $categories['sets']->id,
                'name' => 'Core scrub set',
                'slug' => 'core-scrub-set',
                'short_description' => 'Ready-made set sold by size and color from stock.',
                'description' => 'Sample development product. Fabric composition is stored only when a merchandiser enters it. This row does not claim a certification, medical grade, or country of origin.',
                'gender' => 'unisex',
                'garment_type' => 'set',
                'fulfillment_type' => 'ready',
                'regular_price' => '78.0000',
                'sale_price' => '68.0000',
                'currency_code' => 'USD',
                'fit' => 'regular',
                'care' => 'Follow the care label sewn into the garment.',
                'weight_grams' => 420,
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'meta_title' => 'Core scrub set',
                'meta_description' => 'Ready-made unisex scrub set.',
                'keywords' => 'scrub set unisex',
                'low_stock_threshold' => 3,
                'track_inventory' => true,
            ],
        );

        ProductImage::query()->updateOrCreate(
            ['product_id' => $set->id, 'path' => 'images/placeholders/scrub.svg'],
            ['alt' => 'Illustration of a scrub set', 'is_primary' => true, 'sort_order' => 1],
        );

        $this->variant($set, 'TA-SET-CORE-M-NAVY', 'size:m|color:navy', 12, $sizes['M'], $colors['navy']);
        $this->variant($set, 'TA-SET-CORE-L-TEAL', 'size:l|color:teal', 2, $sizes['L'], $colors['teal']);

        $this->simple('TA-TOP-MEN', 'mens-scrub-top', 'Men’s scrub top', 'men', 'top', $categories['tops']->id, '64.0000', null);
        $this->simple('TA-PANT-WOMEN', 'womens-scrub-pants', 'Women’s scrub pants', 'women', 'pants', $categories['pants']->id, '42.0000', null);
        $this->simple('TA-JACKET', 'lab-coat', 'Lab coat', 'unisex', 'jacket', $categories['jackets']->id, '88.0000', null);
        $this->simple('TA-CAP', 'scrub-cap', 'Scrub cap', 'unisex', 'accessory', $categories['accessories']->id, '16.0000', '12.0000');

        $custom = Product::query()->updateOrCreate(
            ['sku' => 'TA-CUSTOM-TOP'],
            [
                'category_id' => $categories['tops']->id,
                'name' => 'Custom stitched top',
                'slug' => 'custom-stitched-top',
                'short_description' => 'Made after you choose fabric, style, and size or measurements.',
                'description' => 'Custom orders are produced to the configuration you confirm. They are not drawn from ready-made size and color stock.',
                'gender' => 'unisex',
                'garment_type' => 'top',
                'fulfillment_type' => 'custom',
                'regular_price' => '75.0000',
                'currency_code' => 'USD',
                'fit' => 'regular',
                'weight_grams' => 280,
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDay(),
                'production_lead_days' => 14,
                'allows_personalization' => true,
                'track_inventory' => false,
                'meta_title' => 'Custom stitched scrub top',
                'meta_description' => 'Configure a custom scrub top.',
            ],
        );

        ProductImage::query()->updateOrCreate(
            ['product_id' => $custom->id, 'path' => 'images/placeholders/scrub.svg'],
            ['alt' => 'Illustration of a custom scrub top', 'is_primary' => true, 'sort_order' => 1],
        );

        $fabric = $custom->fabrics()->updateOrCreate(
            ['name' => 'Woven scrub cloth'],
            ['composition' => null, 'price_delta' => '5.0000', 'extra_lead_days' => 2, 'is_active' => true, 'sort_order' => 1],
        );
        unset($fabric);

        $collar = $custom->customizationOptions()->updateOrCreate(
            ['code' => 'collar'],
            ['name' => 'Collar', 'step' => 4, 'input_type' => 'select', 'is_required' => true, 'is_active' => true, 'sort_order' => 1],
        );
        $collar->values()->updateOrCreate(['value' => 'v-neck'], ['label' => 'V-neck', 'price_delta' => '0.0000', 'extra_lead_days' => 0, 'is_active' => true, 'sort_order' => 1]);
        $collar->values()->updateOrCreate(['value' => 'mandarin'], ['label' => 'Mandarin', 'price_delta' => '4.0000', 'extra_lead_days' => 1, 'is_active' => true, 'sort_order' => 2]);

        $personalization = $custom->customizationOptions()->updateOrCreate(
            ['code' => 'personalization'],
            ['name' => 'Personalization', 'step' => 7, 'input_type' => 'select', 'is_required' => false, 'is_active' => true, 'sort_order' => 2],
        );
        $personalization->values()->updateOrCreate(['value' => 'text'], ['label' => 'Embroidery text', 'price_delta' => '8.0000', 'extra_lead_days' => 1, 'is_active' => true, 'sort_order' => 1]);
        $personalization->values()->updateOrCreate(['value' => 'logo'], ['label' => 'Logo', 'price_delta' => '15.0000', 'extra_lead_days' => 2, 'is_active' => true, 'sort_order' => 2]);

        $custom->measurementSchemas()->updateOrCreate(
            ['field_key' => 'chest'],
            ['label' => 'Chest', 'min_cm' => '70.00', 'max_cm' => '160.00', 'is_required' => true, 'sort_order' => 1],
        );
        $custom->measurementSchemas()->updateOrCreate(
            ['field_key' => 'length'],
            ['label' => 'Length', 'min_cm' => '50.00', 'max_cm' => '110.00', 'is_required' => true, 'sort_order' => 2],
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'WELCOME10'],
            ['type' => 'percent', 'value' => '10.0000', 'currency_code' => 'USD', 'is_active' => true, 'per_customer_limit' => 1],
        );

        Banner::query()->where('title', 'Ready-made and made to measure')->update([
            'title' => 'Ready-made scrub suits',
        ]);

        Banner::query()->updateOrCreate(
            ['title' => 'Ready-made scrub suits'],
            [
                'subtitle' => 'Stocked sizes ship from inventory.',
                'link_url' => '/shop',
                'placement' => 'home',
                'is_active' => true,
                'sort_order' => 1,
            ],
        );
    }

    private function simple(string $sku, string $slug, string $name, string $gender, string $type, int $categoryId, string $price, ?string $sale): void
    {
        $product = Product::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $slug,
                'short_description' => 'Ready-made sample for the '.$type.' catalog.',
                'gender' => $gender,
                'garment_type' => $type,
                'fulfillment_type' => 'ready',
                'regular_price' => $price,
                'sale_price' => $sale,
                'currency_code' => 'USD',
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'track_inventory' => true,
                'weight_grams' => 250,
            ],
        );

        ProductImage::query()->updateOrCreate(
            ['product_id' => $product->id, 'path' => 'images/placeholders/scrub.svg'],
            ['alt' => $name, 'is_primary' => true, 'sort_order' => 1],
        );

        ProductVariant::query()->updateOrCreate(
            ['sku' => $sku.'-OS'],
            [
                'product_id' => $product->id,
                'combination_key' => 'size:m|color:navy',
                'stock_on_hand' => 10,
                'reserved' => 0,
                'is_available' => true,
                'weight_grams' => 250,
            ],
        );
    }

    private function variant(Product $product, string $sku, string $key, int $stock, AttributeValue $size, AttributeValue $color): void
    {
        $variant = ProductVariant::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'product_id' => $product->id,
                'combination_key' => $key,
                'stock_on_hand' => $stock,
                'reserved' => 0,
                'is_available' => true,
                'weight_grams' => 420,
            ],
        );
        $variant->attributeValues()->sync([$size->id, $color->id]);
    }
}
