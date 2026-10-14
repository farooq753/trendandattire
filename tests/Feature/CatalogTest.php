<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreFixture::basics();
        $this->seed(CatalogSeeder::class);
    }

    public function test_images_keep_the_subdirectory_from_the_application_url(): void
    {
        URL::useAssetOrigin('https://quicksoftsolution.com/public');

        $this->get('/')
            ->assertOk()
            ->assertSee('https://quicksoftsolution.com/public/images/placeholders/scrub.svg', false)
            ->assertDontSee('src="/images/placeholders/scrub.svg"', false);

        $product = Product::query()->where('slug', 'core-scrub-set')->firstOrFail();

        $this->get('/products/'.$product->slug)
            ->assertOk()
            ->assertSee('https://quicksoftsolution.com/public/images/placeholders/scrub.svg', false);
    }

    public function test_storefront_pages_render(): void
    {
        foreach ([
            'home', 'shop', 'men', 'women', 'unisex', 'tops', 'pants', 'sets', 'jackets',
            'accessories', 'new-arrivals', 'best-sellers', 'sale', 'custom.index',
            'about', 'contact', 'faq', 'shipping', 'returns.policy', 'privacy', 'terms',
            'size-guide', 'measurement-guide', 'login', 'register', 'password.request',
            'tracking.create', 'cart.show', 'sitemap', 'robots',
        ] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_search_and_filters_use_catalog_data(): void
    {
        $this->get(route('search', ['q' => 'Core']))->assertOk()->assertSee('Core scrub set');
        $this->get(route('shop', ['gender' => 'women']))->assertOk()->assertSee('Women’s scrub pants')->assertDontSee('Men’s scrub top');
        $this->get(route('shop', ['fulfillment' => 'custom']))->assertOk()->assertSee('Custom stitched top');

        $hidden = Product::factory()->create(['is_published' => false, 'name' => 'Hidden draft scrub']);
        $this->get(route('shop'))->assertOk()->assertDontSee($hidden->name);
    }

    public function test_product_page_lists_available_variation(): void
    {
        $product = Product::query()->where('slug', 'core-scrub-set')->firstOrFail();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('TA-SET-CORE')
            ->assertSee('application/ld+json', false)
            ->assertDontSee('cost_price');
    }
}
