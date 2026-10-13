<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Str;

class CartService
{
    public function __construct(
        private CurrencyService $currencies,
        private CustomConfigurator $configurator,
    ) {}

    public function current(?User $user, ?string $guestToken): Cart
    {
        if ($user) {
            return Cart::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['currency_code' => $this->currencies->defaultCode()],
            );
        }

        $token = $guestToken ?: (string) Str::uuid();

        return Cart::query()->firstOrCreate(
            ['guest_token' => $token],
            ['currency_code' => $this->currencies->defaultCode()],
        );
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @param  array<int, array<string, string>>  $measurements
     * @param  array<string, mixed>|null  $personalization
     */
    public function add(
        Cart $cart,
        Product $product,
        ?ProductVariant $variant,
        string $fulfillment,
        int $quantity,
        string $unitPrice,
        array $configuration = [],
        array $measurements = [],
        ?array $personalization = null,
    ): CartItem {
        $signature = $this->signature($product->id, $variant?->id, $fulfillment, $configuration, $measurements, $personalization);
        $existing = $cart->items()->where('line_signature', $signature)->first();

        if ($existing) {
            $existing->quantity += $quantity;
            $existing->unit_price = $unitPrice;
            $existing->save();

            return $existing;
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'fulfillment_type' => $fulfillment,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'currency_code' => $product->currency_code,
            'configuration' => $configuration,
            'measurements' => $measurements,
            'personalization' => $personalization,
            'line_signature' => $signature,
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity < 1) {
            $item->delete();

            return;
        }

        $item->quantity = $quantity;
        $item->save();
    }

    public function applyCoupon(Cart $cart, ?string $code): void
    {
        $cart->coupon_code = $code ? strtoupper(trim($code)) : null;
        $cart->save();
    }

    public function merge(?string $guestToken, User $user): void
    {
        if (! $guestToken) {
            return;
        }

        $guest = Cart::query()->where('guest_token', $guestToken)->with('items')->first();
        $userCart = $this->current($user, null);

        if (! $guest || $guest->id === $userCart->id) {
            return;
        }

        foreach ($guest->items as $item) {
            $existing = $userCart->items()->where('line_signature', $item->line_signature)->first();

            if ($existing) {
                $existing->quantity += $item->quantity;
                $existing->unit_price = $item->unit_price;
                $existing->save();
                $item->delete();
            } else {
                $item->cart_id = $userCart->id;
                $item->save();
            }
        }

        if ($guest->coupon_code && ! $userCart->coupon_code) {
            $userCart->coupon_code = $guest->coupon_code;
            $userCart->save();
        }

        $guest->delete();
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @param  array<int, array<string, string>>  $measurements
     * @param  array<string, mixed>|null  $personalization
     */
    public function signature(int $productId, ?int $variantId, string $fulfillment, array $configuration, array $measurements, ?array $personalization): string
    {
        $payload = json_encode([
            'product' => $productId,
            'variant' => $variantId,
            'fulfillment' => $fulfillment,
            'configuration' => $configuration,
            'measurements' => $measurements,
            'personalization' => $personalization,
        ]);

        return hash('sha256', $payload ?: '');
    }

    public function readyPrice(Product $product, ProductVariant $variant): string
    {
        return Money::round($variant->unitPrice(), 4);
    }
}
