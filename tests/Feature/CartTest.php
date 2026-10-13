<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_math_coupon_and_guest_merge(): void
    {
        StoreFixture::basics();
        $product = StoreFixture::readyProduct(8, '40.0000');
        $variant = $product->variants->first();
        $carts = app(CartService::class);
        $guest = $carts->current(null, 'guest-token-1');
        $carts->add($guest, $product, $variant, 'ready', 2, '1.00');
        Coupon::factory()->create(['code' => 'SAVE10', 'type' => 'percent', 'value' => '10.0000']);
        $carts->applyCoupon($guest, 'save10');

        $quote = app(CheckoutService::class)->quote($guest->fresh(), [
            'currency_code' => 'USD',
            'country_code' => 'US',
            'region' => 'NY',
            'email' => 'guest@example.com',
        ]);

        $this->assertSame('80.00', $quote['subtotal']);
        $this->assertSame('8.00', $quote['discount']);
        $this->assertSame('72.00', $quote['total']);

        $user = User::factory()->create();
        $carts->merge('guest-token-1', $user);
        $merged = Cart::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(2, (int) $merged->items()->sum('quantity'));
        $this->assertSame('SAVE10', $merged->coupon_code);
        $this->assertNull(Cart::query()->where('guest_token', 'guest-token-1')->first());
    }

    public function test_wishlist_requires_an_account(): void
    {
        StoreFixture::basics();
        $product = StoreFixture::readyProduct();
        $user = User::factory()->create();

        $this->post(route('wishlist.store', $product))->assertRedirect(route('login'));
        $this->actingAs($user)->post(route('wishlist.store', $product))->assertRedirect();
        $this->actingAs($user)->get(route('wishlist.index'))->assertOk()->assertSee($product->name);
    }
}
