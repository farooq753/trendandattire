<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\CustomConfigurator;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class CustomConfiguratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreFixture::basics();
        $this->seed(CatalogSeeder::class);
    }

    public function test_missing_required_options_are_rejected(): void
    {
        $product = Product::query()->where('slug', 'custom-stitched-top')->firstOrFail();
        $priced = app(CustomConfigurator::class)->price($product, [
            'sizing_method' => 'standard',
            'standard_size' => 'M',
        ]);

        $this->assertFalse($priced['ok']);
        $this->assertArrayHasKey('fabric_id', $priced['errors']);
        $this->assertArrayHasKey('options.collar', $priced['errors']);
    }

    public function test_inches_are_stored_as_entered_and_normalized_to_centimeters(): void
    {
        $product = Product::query()->where('slug', 'custom-stitched-top')->firstOrFail();
        $fabric = $product->fabrics()->firstOrFail();
        $collar = $product->customizationOptions()->where('code', 'collar')->firstOrFail()->values()->where('value', 'mandarin')->firstOrFail();

        $priced = app(CustomConfigurator::class)->price($product, [
            'fabric_id' => $fabric->id,
            'options' => ['collar' => $collar->id],
            'sizing_method' => 'custom',
            'measurements' => [
                'chest' => ['value' => '40', 'unit' => 'in'],
                'length' => ['value' => '70', 'unit' => 'cm'],
            ],
            'personalization_type' => 'text',
            'personalization_text' => 'Amina',
        ]);

        $this->assertTrue($priced['ok'], json_encode($priced['errors']));
        $this->assertSame('92.0000', $priced['unit_price']);
        $this->assertSame(18, $priced['lead_days']);
        $chest = collect($priced['measurements'])->firstWhere('field_key', 'chest');
        $this->assertSame('40.00', $chest['original_value']);
        $this->assertSame('in', $chest['original_unit']);
        $this->assertSame('101.60', $chest['normalized_cm']);
    }

    public function test_configure_page_is_not_available(): void
    {
        $this->get('/products/custom-stitched-top/configure')->assertNotFound();
        $this->get('/products/custom-stitched-top')->assertNotFound();
    }
}
