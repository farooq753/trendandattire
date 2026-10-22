<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        Currency::query()->updateOrCreate(
            ['code' => 'PKR'],
            ['name' => 'Pakistani Rupee', 'symbol' => 'Rs. ', 'decimal_places' => 0, 'is_active' => true, 'is_default' => true],
        );
        Currency::query()->where('code', '!=', 'PKR')->update(['is_default' => false]);

        $groups = [
            'sets' => 'Sets',
            'jackets' => 'Jackets and lab coats',
            'accessories' => 'Accessories',
        ];

        $categories = [];

        foreach ($groups as $slug => $name) {
            $categories[$slug] = Category::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'nav_group' => $slug,
                    'is_active' => true,
                    'sort_order' => count($categories),
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
        foreach (['S', 'M', 'L', 'XL', '2XL', 'Standard'] as $index => $label) {
            $sizes[$label] = AttributeValue::query()->updateOrCreate(
                ['product_attribute_id' => $size->id, 'slug' => strtolower($label)],
                ['value' => $label, 'sort_order' => $index],
            );
        }

        AttributeValue::query()
            ->where('product_attribute_id', $size->id)
            ->whereNotIn('slug', array_map(strtolower(...), array_keys($sizes)))
            ->delete();

        $palette = [
            'navy' => ['Navy', '#031531'],
            'black' => ['Black', '#111111'],
            'army-green' => ['Army Green', '#254323'],
            'royal-blue' => ['Royal Blue', '#022C7D'],
            'sky-blue' => ['Sky Blue', '#3D80BE'],
            'grey' => ['Grey', '#484848'],
            'maroon' => ['Maroon', '#68071C'],
            'teal' => ['Teal', '#168B8B'],
            'white' => ['White', '#FFFFFF'],
        ];

        $colors = [];
        foreach ($palette as $slug => [$label, $hex]) {
            $colors[$slug] = AttributeValue::query()->updateOrCreate(
                ['product_attribute_id' => $color->id, 'slug' => $slug],
                ['value' => $label, 'meta' => ['hex' => $hex], 'sort_order' => count($colors)],
            );
        }

        AttributeValue::query()
            ->where('product_attribute_id', $color->id)
            ->whereNotIn('slug', array_keys($palette))
            ->delete();

        $scrubImages = [
            ['path' => 'images/products/scrub-set.jpg', 'alt' => 'Scrub top, pants, and cap'],
            ['path' => 'images/products/scrub-side.jpg', 'alt' => 'Side view of the scrub suit'],
            ['path' => 'images/products/scrub-top.jpg', 'alt' => 'Scrub top with a chest pocket'],
            ['path' => 'images/products/scrub-pants.jpg', 'alt' => 'Scrub pants with a drawstring waist'],
            ['path' => 'images/products/scrub-neck.jpg', 'alt' => 'V-neck and chest pocket detail'],
            ['path' => 'images/products/scrub-pocket.jpg', 'alt' => 'Close view of the chest pocket'],
            ['path' => 'images/products/scrub-hem.jpg', 'alt' => 'Side pocket and hem detail'],
        ];

        $scrubColors = array_diff_key($colors, ['white' => true]);
        $scrubSizes = ['S', 'M', 'L', 'XL', '2XL'];

        $signature = $this->product(
            'TA-SIG',
            'signature-scrub-collection',
            'Signature Scrub Collection',
            $categories['sets']->id,
            'set',
            '6500.0000',
            'Ready to wear. Breathable premium-quality fabric, 90% cotton.',
            "Ready to wear.\nBreathable premium-quality fabric, 90% cotton.\nWrinkle-free.\nColour fast.\nNo shrinkage.",
            '90% cotton',
            true,
        );
        $this->images($signature, $scrubImages);
        $this->matrix($signature, 'TA-SIG', $scrubSizes, $scrubColors, $sizes, 480);

        $essential = $this->product(
            'TA-ESS',
            'essential-scrub-collection',
            'Essential Scrub Collection',
            $categories['sets']->id,
            'set',
            '4500.0000',
            'Ready to wear. Breathable premium-quality fabric, 70% cotton.',
            "Ready to wear.\nBreathable premium-quality fabric, 70% cotton.\nWrinkle-free.\nColour fast.\nNo shrinkage.",
            '70% cotton',
            true,
        );
        $this->images($essential, $scrubImages);
        $this->matrix($essential, 'TA-ESS', $scrubSizes, $scrubColors, $sizes, 420);

        $cap = $this->product(
            'TA-CAP',
            'scrub-cap',
            'Scrub cap',
            $categories['accessories']->id,
            'accessory',
            '650.0000',
            'Discounted price. Standard size, ready to wear.',
            "Discounted price, standard size.\nReady to wear.",
            null,
            false,
        );
        $this->images($cap, [
            ['path' => 'images/products/scrub-cap.jpg', 'alt' => 'Black scrub cap with ties'],
        ]);
        $this->syncVariants($cap, [[
            'sku' => 'TA-CAP-STD',
            'key' => 'size:standard',
            'attributes' => [$sizes['Standard']->id],
            'weight' => 40,
        ]]);

        $coat = $this->product(
            'TA-COAT',
            'white-coat',
            'White coat',
            $categories['jackets']->id,
            'jacket',
            '1850.0000',
            'Discounted price. Ready to wear in sizes S, M, L, and XL.',
            "Discounted price.\nReady to wear.\nSizes: S, M, L, XL.",
            null,
            false,
        );
        $this->images($coat, [
            ['path' => 'images/products/white-coat.jpg', 'alt' => 'White coat, front view'],
            ['path' => 'images/products/white-coat-angle.jpg', 'alt' => 'White coat at a slight angle'],
            ['path' => 'images/products/white-coat-collar.jpg', 'alt' => 'White coat collar and label'],
            ['path' => 'images/products/white-coat-chest.jpg', 'alt' => 'White coat chest pocket'],
            ['path' => 'images/products/white-coat-sleeve.jpg', 'alt' => 'White coat sleeve and side pocket'],
            ['path' => 'images/products/white-coat-pocket.jpg', 'alt' => 'Close view of the white coat pocket'],
        ]);
        $coatRows = [];
        foreach (['S', 'M', 'L', 'XL'] as $label) {
            $coatRows[] = [
                'sku' => 'TA-COAT-'.$label,
                'key' => 'size:'.strtolower($label).'|color:white',
                'attributes' => [$sizes[$label]->id, $colors['white']->id],
                'weight' => 500,
            ];
        }
        $this->syncVariants($coat, $coatRows);

        $custom = Product::query()->updateOrCreate(
            ['sku' => 'TA-CUSTOM-TOP'],
            [
                'category_id' => $categories['sets']->id,
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
                'is_featured' => false,
                'is_published' => false,
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

        $custom->fabrics()->updateOrCreate(
            ['name' => 'Woven scrub cloth'],
            ['composition' => null, 'price_delta' => '5.0000', 'extra_lead_days' => 2, 'is_active' => true, 'sort_order' => 1],
        );

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

        Product::query()
            ->whereNotIn('sku', ['TA-SIG', 'TA-ESS', 'TA-CAP', 'TA-COAT'])
            ->update(['is_published' => false]);

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
                'subtitle' => 'Signature and Essential collections, ready to wear.',
                'link_url' => '/shop',
                'placement' => 'home',
                'is_active' => true,
                'sort_order' => 1,
            ],
        );
    }

    private function product(
        string $sku,
        string $slug,
        string $name,
        int $categoryId,
        string $type,
        string $price,
        string $summary,
        string $description,
        ?string $fabric,
        bool $featured,
    ): Product {
        return Product::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $slug,
                'short_description' => $summary,
                'description' => $description,
                'gender' => 'unisex',
                'garment_type' => $type,
                'fulfillment_type' => 'ready',
                'regular_price' => $price,
                'sale_price' => null,
                'currency_code' => 'PKR',
                'fabric_composition' => $fabric,
                'fit' => 'regular',
                'care' => $fabric ? 'Wrinkle-free, colour fast, and no shrinkage.' : null,
                'weight_grams' => $type === 'set' ? 450 : 200,
                'is_featured' => $featured,
                'is_published' => true,
                'published_at' => now()->subDays($featured ? 2 : 1),
                'meta_title' => $name,
                'meta_description' => $summary,
                'keywords' => $name,
                'low_stock_threshold' => 3,
                'track_inventory' => true,
                'allows_personalization' => false,
            ],
        );
    }

    /**
     * @param  array<int, array{path: string, alt: string}>  $images
     */
    private function images(Product $product, array $images): void
    {
        $paths = array_column($images, 'path');
        $product->images()->whereNotIn('path', $paths)->delete();

        foreach ($images as $index => $image) {
            ProductImage::query()->updateOrCreate(
                ['product_id' => $product->id, 'path' => $image['path']],
                ['alt' => $image['alt'], 'is_primary' => $index === 0, 'sort_order' => $index + 1],
            );
        }
    }

    /**
     * @param  array<int, string>  $sizeLabels
     * @param  array<string, AttributeValue>  $colors
     * @param  array<string, AttributeValue>  $sizes
     */
    private function matrix(Product $product, string $prefix, array $sizeLabels, array $colors, array $sizes, int $weight): void
    {
        $rows = [];

        foreach ($sizeLabels as $label) {
            foreach ($colors as $slug => $color) {
                $rows[] = [
                    'sku' => $prefix.'-'.$label.'-'.strtoupper($slug),
                    'key' => 'size:'.strtolower($label).'|color:'.$slug,
                    'attributes' => [$sizes[$label]->id, $color->id],
                    'weight' => $weight,
                ];
            }
        }

        $this->syncVariants($product, $rows);
    }

    /**
     * @param  array<int, array{sku: string, key: string, attributes: array<int, int>, weight: int}>  $rows
     */
    private function syncVariants(Product $product, array $rows): void
    {
        $product->variants()->whereNotIn('sku', array_column($rows, 'sku'))->get()->each->delete();

        foreach ($rows as $row) {
            $variant = ProductVariant::query()->updateOrCreate(
                ['sku' => $row['sku']],
                [
                    'product_id' => $product->id,
                    'combination_key' => $row['key'],
                    'stock_on_hand' => 15,
                    'reserved' => 0,
                    'is_available' => true,
                    'weight_grams' => $row['weight'],
                ],
            );
            $variant->attributeValues()->sync($row['attributes']);
        }
    }
}
