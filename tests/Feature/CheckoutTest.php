<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_validation_cod_tax_shipping_and_duplicate_submission(): void
    {
        $method = StoreFixture::basics();
        $product = StoreFixture::readyProduct(3, '50.0000');
        $variant = $product->variants->first();
        $token = Str::random(40);

        $this->withSession([
            'guest_cart' => 'guest-checkout',
            'currency_code' => 'USD',
            'checkout_token' => $token,
        ])->post(route('products.cart', $product), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.show'));

        $this->withSession([
            'guest_cart' => 'guest-checkout',
            'currency_code' => 'USD',
            'checkout_token' => $token,
        ])->post(route('checkout.store'), [
            'checkout_token' => $token,
            'payment_method' => 'cod',
        ])->assertSessionHasErrors(['name', 'email', 'phone', 'shipping_method_id']);

        $payload = $this->payload($method->id, $token, 'cod', 'CA');

        $this->withSession([
            'guest_cart' => 'guest-checkout',
            'currency_code' => 'USD',
            'checkout_token' => $token,
        ])->post(route('checkout.store'), $payload)->assertRedirect();

        $order = Order::query()->where('checkout_token', $token)->firstOrFail();
        $this->assertSame('63.6300', $order->grand_total);
        $this->assertSame('3.6300', $order->tax_total);
        $this->assertSame('8.0000', $order->shipping_total);
        $this->assertSame('2.0000', $order->cod_fee);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(2, $variant->fresh()->stock_on_hand);

        $this->withSession([
            'guest_cart' => 'guest-checkout',
            'currency_code' => 'USD',
            'checkout_token' => $token,
        ])->post(route('checkout.store'), $payload)->assertRedirect(route('checkout.confirmation', $order));

        $this->assertSame(1, Order::query()->count());
        $this->assertSame(2, $variant->fresh()->stock_on_hand);
    }

    public function test_currency_snapshot_uses_the_saved_rate(): void
    {
        $method = StoreFixture::basics();
        $product = StoreFixture::readyProduct(2, '100.0000');
        $variant = $product->variants->first();
        $token = Str::random(40);

        $this->withSession(['guest_cart' => 'guest-eur', 'currency_code' => 'EUR'])
            ->post(route('products.cart', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $this->withSession([
            'guest_cart' => 'guest-eur',
            'currency_code' => 'EUR',
            'checkout_token' => $token,
        ])->post(route('checkout.store'), $this->payload($method->id, $token, 'cod', 'CA'))
            ->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame('EUR', $order->currency_code);
        $this->assertSame('0.92000000', $order->fx_rate);
        $this->assertSame('USD', $order->fx_base_code);
        $this->assertNotNull($order->fx_quoted_at);
        $this->assertSame('107.8700', $order->grand_total);
    }

    public function test_online_methods_cannot_place_an_order(): void
    {
        $method = StoreFixture::basics();
        $product = StoreFixture::readyProduct();
        $variant = $product->variants->first();
        $token = Str::random(40);

        $this->withSession(['guest_cart' => 'guest-card', 'currency_code' => 'USD'])
            ->post(route('products.cart', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $this->withSession([
            'guest_cart' => 'guest-card',
            'currency_code' => 'USD',
            'checkout_token' => $token,
        ])->post(route('checkout.store'), $this->payload($method->id, $token, 'stripe'))
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(5, ProductVariant::query()->find($variant->id)->stock_on_hand);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(int $methodId, string $token, string $payment, string $region = 'NY'): array
    {
        return [
            'checkout_token' => $token,
            'name' => 'Amina Shah',
            'email' => 'amina@example.com',
            'phone' => '+12025550123',
            'shipping_method_id' => $methodId,
            'payment_method' => $payment,
            'billing_same' => '1',
            'shipping' => [
                'name' => 'Amina Shah',
                'phone' => '+12025550123',
                'country_code' => 'US',
                'region' => $region,
                'city' => 'Oakland',
                'line1' => '1 Market Street',
                'postal_code' => '94612',
            ],
        ];
    }
}
