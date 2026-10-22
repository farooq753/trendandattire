<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\CheckoutException;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Payment;
use App\Models\ProductionJob;
use App\Models\ShippingMethod;
use App\Models\StoreSetting;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private CurrencyService $currencies,
        private CouponService $coupons,
        private ShippingService $shipping,
        private TaxService $tax,
        private InventoryService $inventory,
        private StoreSettings $settings,
        private OrderNotifier $notifier,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function quote(Cart $cart, array $input, bool $lock = false): array
    {
        $cart->load(['items.product.fabrics', 'items.product.customizationOptions.values', 'items.product.measurementSchemas', 'items.variant']);
        $currencyCode = strtoupper((string) ($input['currency_code'] ?? $this->currencies->defaultCode()));

        if (! $this->currencies->canCheckoutIn($currencyCode)) {
            $currencyCode = $this->currencies->defaultCode();
        }

        $currency = Currency::query()->find($currencyCode);
        $scale = (int) ($currency->decimal_places ?? 2);
        $base = $this->currencies->defaultCode();
        $errors = [];
        $lines = [];
        $subtotal = '0';
        $weight = 0;
        $fx = $this->currencies->convert('1', $base, $currencyCode);

        foreach ($cart->items as $item) {
            $product = $item->product;
            $lineError = null;
            $unit = '0';
            $lead = (int) ($product?->production_lead_days ?? 0);
            $configuration = $item->configuration ?? [];
            $measurements = $item->measurements ?? [];
            $personalization = $item->personalization;

            if (! $product || ! $product->is_published) {
                $lineError = 'This product is no longer available.';
            } elseif ($item->fulfillment_type === 'ready') {
                $variant = $item->variant;

                if ($lock && $variant) {
                    $variant = $variant->newQuery()->whereKey($variant->id)->lockForUpdate()->first();
                }

                if (! $variant || ! $variant->is_available || ! $product->acceptsReadyMade()) {
                    $lineError = 'That size and color combination is unavailable.';
                } elseif ($product->track_inventory && $this->inventory->available($variant) < $item->quantity) {
                    $lineError = 'Not enough stock is available for that quantity.';
                } else {
                    $unit = $variant->unitPrice();
                    $weight += ((int) ($variant->weight_grams ?: $product->weight_grams ?: 0)) * $item->quantity;
                }
            } elseif ($item->fulfillment_type === 'custom') {
                $lineError = 'Custom stitching is no longer available.';
            } else {
                $lineError = 'This line has an unknown fulfillment type.';
            }

            if ($lineError) {
                $errors['line_'.$item->id] = $lineError;
            }

            try {
                $converted = $this->currencies->convert($unit, $product->currency_code ?? $base, $currencyCode);
            } catch (\RuntimeException $exception) {
                $errors['currency'] = $exception->getMessage();
                $converted = ['amount' => '0.00', 'rate' => $fx['rate'], 'quoted_at' => $fx['quoted_at']];
            }

            $unitRounded = Money::round($converted['amount'], $scale);
            $lineTotal = Money::round(Money::mul($unitRounded, (string) $item->quantity), $scale);
            $subtotal = Money::add($subtotal, $lineTotal);
            $lines[] = [
                'item' => $item,
                'name' => $product->name ?? 'Unavailable item',
                'sku' => $item->variant->sku ?? $product->sku ?? '',
                'unit_price' => $unitRounded,
                'line_total' => $lineTotal,
                'quantity' => $item->quantity,
                'fulfillment' => $item->fulfillment_type,
                'variant' => $item->variant,
                'lead_days' => $lead,
                'configuration' => $configuration,
                'measurements' => $measurements,
                'personalization' => $personalization,
                'error' => $lineError,
                'snapshot' => [
                    'name' => $product->name ?? null,
                    'sku' => $item->variant->sku ?? $product->sku ?? null,
                    'currency_code' => $product->currency_code ?? $base,
                    'catalog_unit_price' => $unit,
                ],
            ];
        }

        $subtotal = Money::round($subtotal, $scale);
        $discount = '0';
        $discountError = null;

        if ($cart->coupon_code) {
            $result = $this->coupons->discount($cart->coupon_code, $subtotal, $currencyCode, $cart->user, $input['email'] ?? $cart->user?->email);

            if ($result['ok']) {
                $discount = Money::round($result['discount'], $scale);
            } else {
                $discountError = $result['error'];
                $errors['coupon'] = $result['error'];
            }
        }

        $discounted = Money::sub($subtotal, $discount);
        $shippingOptions = collect();
        $shippingAmount = '0';
        $selectedMethod = null;
        $country = $input['country_code'] ?? null;

        if ($country) {
            $baseSubtotal = $currencyCode === $base
                ? $discounted
                : Money::div($discounted, $fx['rate'] ?: '1', 8);
            $shippingOptions = $this->shipping->quotes($country, $input['region'] ?? null, Money::round($baseSubtotal, 4), $weight);

            if (! empty($input['shipping_method_id'])) {
                $selected = $shippingOptions->first(fn (array $quote) => (int) $quote['method']->id === (int) $input['shipping_method_id']);

                if (! $selected) {
                    $errors['shipping_method_id'] = 'Choose a shipping method available for that address.';
                } else {
                    $selectedMethod = $selected['method'];
                    $shippingAmount = $this->currencies->convert($selected['amount'], $base, $currencyCode)['amount'];
                    $shippingAmount = Money::round($shippingAmount, $scale);
                }
            }
        }

        $tax = ['total' => '0.0000', 'inclusive' => false, 'lines' => []];

        if ($country) {
            $tax = $this->tax->calculate($country, $input['region'] ?? null, $discounted, $shippingAmount);
            $tax['total'] = Money::round($tax['total'], $scale);
        }

        $codFee = '0';
        $paymentMethod = $input['payment_method'] ?? null;

        if ($paymentMethod === PaymentMethod::Cod->value) {
            $codCheck = $this->codAllowed($selectedMethod, $country, Money::add($discounted, $shippingAmount));

            if ($codCheck !== true) {
                $errors['payment_method'] = $codCheck;
            } else {
                $codFee = Money::round($this->currencies->convert((string) $this->settings->get('cod_fee', '0'), $base, $currencyCode)['amount'], $scale);
            }
        }

        if (in_array($paymentMethod, [PaymentMethod::Stripe->value, PaymentMethod::Paypal->value], true)) {
            $errors['payment_method'] = 'Card and PayPal checkout stay disabled until a provider account is connected. They cannot complete an order from this screen.';
        }

        $total = Money::round(Money::add(Money::add(Money::add($discounted, $shippingAmount), $tax['total']), $codFee), $scale);

        return [
            'lines' => $lines,
            'currency' => $currencyCode,
            'scale' => $scale,
            'fx_base' => $base,
            'fx_rate' => $fx['rate'],
            'fx_quoted_at' => $fx['quoted_at'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_error' => $discountError,
            'shipping' => $shippingAmount,
            'shipping_options' => $shippingOptions,
            'shipping_method' => $selectedMethod,
            'tax' => $tax['total'],
            'tax_lines' => $tax['lines'],
            'tax_snapshot' => $tax,
            'cod_fee' => $codFee,
            'total' => $total,
            'weight' => $weight,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function place(Cart $cart, array $input, ?User $user): Order
    {
        $token = (string) ($input['checkout_token'] ?? '');

        if ($token === '') {
            throw new CheckoutException(['checkout_token' => 'Refresh checkout and submit the order again.']);
        }

        try {
            $order = DB::transaction(function () use ($cart, $input, $user, $token) {
                $existing = Order::query()->where('checkout_token', $token)->lockForUpdate()->first();

                if ($existing) {
                    return $existing;
                }

                Cart::query()->whereKey($cart->id)->lockForUpdate()->first();
                $quote = $this->quote($cart->fresh(), $input, true);

                if ($quote['errors'] !== [] || $quote['lines'] === []) {
                    throw new CheckoutException($quote['errors'] ?: ['cart' => 'Your cart is empty.']);
                }

                if (! $quote['shipping_method']) {
                    throw new CheckoutException(['shipping_method_id' => 'Choose a shipping method.']);
                }

                $order = Order::query()->create([
                    'number' => $this->nextNumber(),
                    'user_id' => $user?->id,
                    'tracking_token' => bin2hex(random_bytes(32)),
                    'checkout_token' => $token,
                    'currency_code' => $quote['currency'],
                    'fx_base_code' => $quote['fx_base'],
                    'fx_rate' => $quote['fx_rate'],
                    'fx_quoted_at' => $quote['fx_quoted_at'],
                    'subtotal' => $quote['subtotal'],
                    'discount_total' => $quote['discount'],
                    'shipping_total' => $quote['shipping'],
                    'tax_total' => $quote['tax'],
                    'cod_fee' => $quote['cod_fee'],
                    'grand_total' => $quote['total'],
                    'coupon_code' => $quote['discount'] !== '0' && Money::compare($quote['discount'], '0') > 0 ? $cart->coupon_code : null,
                    'payment_method' => $input['payment_method'],
                    'payment_status' => $input['payment_method'] === PaymentMethod::BankTransfer->value
                        ? PaymentStatus::AwaitingPayment->value
                        : PaymentStatus::Pending->value,
                    'fulfillment_status' => FulfillmentStatus::Unfulfilled->value,
                    'customer_name' => $input['name'],
                    'customer_email' => $input['email'],
                    'customer_phone' => $input['phone'],
                    'notes' => $input['notes'] ?? null,
                    'shipping_method_name' => $quote['shipping_method']->name,
                    'shipping_snapshot' => [
                        'method_id' => $quote['shipping_method']->id,
                        'code' => $quote['shipping_method']->code,
                        'name' => $quote['shipping_method']->name,
                        'processing_min_days' => $quote['shipping_method']->processing_min_days,
                        'processing_max_days' => $quote['shipping_method']->processing_max_days,
                        'estimate_note' => 'Shipping times are estimates, not guarantees. Carrier rates are not live.',
                    ],
                    'tax_snapshot' => $quote['tax_snapshot'],
                    'placed_at' => now(),
                ]);

                $this->storeAddress($order, 'shipping', $input['shipping']);
                $this->storeAddress($order, 'billing', ($input['billing_same'] ?? true) ? $input['shipping'] : $input['billing']);

                foreach ($quote['lines'] as $line) {
                    $item = $order->items()->create([
                        'product_id' => $line['item']->product_id,
                        'product_variant_id' => $line['item']->product_variant_id,
                        'name' => $line['name'],
                        'sku' => $line['sku'],
                        'fulfillment_type' => $line['fulfillment'],
                        'quantity' => $line['quantity'],
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['line_total'],
                        'production_status' => $line['fulfillment'] === 'custom' ? 'awaiting_review' : null,
                        'snapshot' => $line['snapshot'],
                    ]);

                    foreach ($line['configuration'] as $option) {
                        $item->configurations()->create($option);
                    }

                    foreach ($line['measurements'] as $measurement) {
                        $item->measurements()->create($measurement);
                    }

                    if ($line['fulfillment'] === 'ready' && $line['variant']) {
                        $reserved = $input['payment_method'] === PaymentMethod::BankTransfer->value
                            ? $this->inventory->reserve($line['variant'], $line['quantity'], $order)
                            : $this->inventory->deduct($line['variant'], $line['quantity'], $order);

                        if (! $reserved) {
                            throw new CheckoutException(['stock' => 'The last available unit was just purchased.']);
                        }
                    }

                    if ($line['fulfillment'] === 'custom') {
                        ProductionJob::query()->create([
                            'order_item_id' => $item->id,
                            'status' => 'awaiting_review',
                            'lead_time_days' => $line['lead_days'],
                            'due_on' => now()->addDays($line['lead_days'])->toDateString(),
                        ]);
                    }
                }

                if ($cart->coupon_code && Money::compare($quote['discount'], '0') > 0) {
                    $coupon = Coupon::query()->whereRaw('upper(code) = ?', [strtoupper($cart->coupon_code)])->first();

                    if ($coupon) {
                        $coupon->usages()->create([
                            'user_id' => $user?->id,
                            'order_id' => $order->id,
                            'email' => strtolower($input['email']),
                        ]);
                    }
                }

                Payment::query()->create([
                    'order_id' => $order->id,
                    'method' => $input['payment_method'],
                    'status' => $order->payment_status,
                    'amount' => $order->grand_total,
                    'currency_code' => $order->currency_code,
                    'idempotency_key' => $token,
                ]);

                OrderEvent::query()->create([
                    'order_id' => $order->id,
                    'type' => 'placed',
                    'message' => 'Order placed.',
                    'user_id' => $user?->id,
                ]);

                $cart->items()->delete();
                $cart->coupon_code = null;
                $cart->save();

                return $order;
            });
        } catch (UniqueConstraintViolationException) {
            $order = Order::query()->where('checkout_token', $token)->first();

            if (! $order) {
                throw new CheckoutException(['checkout_token' => 'This checkout was already submitted.']);
            }
        }

        if ($order->wasRecentlyCreated) {
            $this->notifier->placed($order);
        }

        return $order;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function storeAddress(Order $order, string $type, array $address): void
    {
        $order->addresses()->create([
            'type' => $type,
            'name' => $address['name'] ?? $order->customer_name,
            'phone' => $address['phone'] ?? $order->customer_phone,
            'country_code' => $address['country_code'],
            'region' => $address['region'] ?? null,
            'city' => $address['city'],
            'line1' => $address['line1'],
            'line2' => $address['line2'] ?? null,
            'postal_code' => $address['postal_code'] ?? null,
        ]);
    }

    private function nextNumber(): string
    {
        $setting = StoreSetting::query()->firstOrCreate(
            ['key' => 'order_sequence'],
            ['value' => ['v' => 0]],
        );
        $setting = StoreSetting::query()->whereKey($setting->id)->lockForUpdate()->firstOrFail();
        $next = ((int) ($setting->value['v'] ?? 0)) + 1;
        $setting->value = ['v' => $next];
        $setting->save();

        return sprintf('TA-%s-%06d', now()->format('Y'), $next);
    }

    private function codAllowed(?ShippingMethod $method, ?string $country, string $merchandiseAndShipping): true|string
    {
        if (! $this->settings->get('cod_enabled', false)) {
            return 'Cash on delivery is not enabled.';
        }

        if (! $method || ! $method->allows_cod) {
            return 'Cash on delivery is not available for that shipping method.';
        }

        $allowed = $this->settings->get('cod_countries', []);

        if (is_array($allowed) && $allowed !== [] && ! in_array($country, $allowed, true)) {
            return 'Cash on delivery is not available in that country.';
        }

        $max = (string) $this->settings->get('cod_max_amount', '0');

        if (Money::compare($max, '0') > 0 && Money::compare($merchandiseAndShipping, $max) > 0) {
            return 'This order is above the cash on delivery limit.';
        }

        return true;
    }
}
