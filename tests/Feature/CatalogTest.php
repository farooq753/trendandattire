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

    public function test_images_include_the_xampp_project_folder(): void
    {
        URL::useAssetOrigin(null);

        $origin = rtrim((string) config('app.url'), '/');

        $this->withServerVariables([
            'DOCUMENT_ROOT' => dirname(public_path()),
        ])->get('/')
            ->assertOk()
            ->assertSee($origin.'/public/images/placeholders/scrub.svg', false)
            ->assertDontSee('src="/images/placeholders/scrub.svg"', false)
            ->assertDontSee('href="/shop"', false);
    }

    public function test_contact_page_shows_the_nazimabad_shop_on_google_maps(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk()
            ->assertSee('Suit F-21, Saima Paari Star', false)
            ->assertSee('North Nazimabad, Karachi 74600', false)
            ->assertSee('https://maps.app.goo.gl/2YCJHnG91GyaQY999', false)
            ->assertSee('0x3eb33fcd52fb56d9', false)
            ->assertSee('24.9414297', false);

        $this->assertStringContainsString(
            'frame-src https://www.google.com',
            (string) $response->headers->get('Content-Security-Policy'),
        );
    }

    public function test_storefront_pages_render(): void
    {
        foreach ([
            'home', 'shop', 'men', 'women', 'unisex', 'tops', 'pants', 'sets', 'jackets',
            'accessories', 'new-arrivals', 'best-sellers', 'sale',
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
        $this->get(route('shop'))->assertOk()->assertDontSee('Custom stitched top');
        $this->get('/custom-scrubs')->assertNotFound();
        $this->get('/products/custom-stitched-top/configure')->assertNotFound();

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
