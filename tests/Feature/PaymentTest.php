<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_transfer_stays_unpaid_until_staff_verify_it(): void
    {
        $method = StoreFixture::basics();
        $product = StoreFixture::readyProduct(2, '40.0000');
        $variant = $product->variants->first();
        $token = Str::random(40);

        $this->withSession(['guest_cart' => 'guest-bank', 'currency_code' => 'USD'])
            ->post(route('products.cart', $product), ['variant_id' => $variant->id, 'quantity' => 1]);

        $this->withSession([
            'guest_cart' => 'guest-bank',
            'currency_code' => 'USD',
            'checkout_token' => $token,
        ])->post(route('checkout.store'), [
            'checkout_token' => $token,
            'name' => 'Amina Shah',
            'email' => 'amina@example.com',
            'phone' => '+12025550123',
            'shipping_method_id' => $method->id,
            'payment_method' => 'bank_transfer',
            'billing_same' => '1',
            'shipping' => [
                'name' => 'Amina Shah',
                'phone' => '+12025550123',
                'country_code' => 'US',
                'region' => 'NY',
                'city' => 'Oakland',
                'line1' => '1 Market Street',
                'postal_code' => '94612',
            ],
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $payment = $order->payment;
        $this->assertSame('awaiting_payment', $payment->status);
        $this->assertSame(1, $variant->fresh()->reserved);
        $this->assertSame(2, $variant->fresh()->stock_on_hand);

        $this->withSession(['tracked.'.$order->tracking_token => true, 'confirmed_order' => $order->id])
            ->post(route('checkout.bank-evidence', $order), [
                'bank_reference' => 'REF-100',
                'customer_note' => 'Sent from my bank.',
            ])->assertRedirect();

        $this->assertSame('evidence_submitted', $payment->fresh()->status);
        $this->assertNotSame('paid', $order->fresh()->payment_status);

        $finance = StoreFixture::userWithRole('finance-manager');
        $this->actingAs($finance)->post(route('admin.payments.verify-bank', $payment))->assertRedirect();

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame(0, $variant->fresh()->reserved);
        $this->assertSame(1, $variant->fresh()->stock_on_hand);
    }

    public function test_cod_stays_unpaid_until_collection_is_confirmed(): void
    {
        StoreFixture::basics();
        $order = Order::factory()->create(['payment_method' => 'cod', 'payment_status' => 'pending']);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'cod',
            'status' => 'pending',
            'amount' => $order->grand_total,
            'currency_code' => 'USD',
        ]);

        $this->assertSame('pending', $payment->status);
        app(PaymentService::class)->confirmCodCollection($payment, 'Collected on delivery.');
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_stripe_webhook_requires_a_signature_and_a_server_side_status(): void
    {
        StoreFixture::basics();
        $order = Order::factory()->create(['payment_method' => 'stripe', 'payment_status' => 'pending']);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'stripe',
            'status' => 'pending',
            'amount' => $order->grand_total,
            'currency_code' => 'USD',
            'provider' => 'stripe',
            'provider_reference' => 'pi_Test123',
        ]);

        $this->postJson('/webhooks/stripe', ['id' => 'evt_1'])->assertStatus(503);
        $this->assertSame('pending', $payment->fresh()->status);

        config([
            'store.payments.stripe.secret' => 'sk_test_boundary',
            'store.payments.stripe.webhook_secret' => 'whsec_test_boundary',
        ]);

        $body = json_encode([
            'id' => 'evt_1',
            'data' => ['object' => ['id' => 'pi_Test123', 'status' => 'succeeded']],
        ], JSON_THROW_ON_ERROR);
        $stamp = (string) time();
        $signature = hash_hmac('sha256', $stamp.'.'.$body, 'whsec_test_boundary');

        $this->call('POST', '/webhooks/stripe', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$stamp.',v1=bad',
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertStatus(400);

        Http::fake([
            'https://api.stripe.com/*' => Http::sequence()
                ->push(['id' => 'pi_Test123', 'status' => 'requires_payment_method'])
                ->push(['id' => 'pi_Test123', 'status' => 'succeeded'])
                ->push(['id' => 'pi_Test123', 'status' => 'succeeded']),
        ]);
        $this->call('POST', '/webhooks/stripe', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$stamp.',v1='.$signature,
            'CONTENT_TYPE' => 'application/json',
        ], $body)->assertOk();
        $this->assertSame('pending', $payment->fresh()->status);

        $paidBody = json_encode([
            'id' => 'evt_paid',
            'data' => ['object' => ['id' => 'pi_Test123', 'status' => 'succeeded']],
        ], JSON_THROW_ON_ERROR);
        $paidSignature = hash_hmac('sha256', $stamp.'.'.$paidBody, 'whsec_test_boundary');
        $paidResponse = $this->call('POST', '/webhooks/stripe', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$stamp.',v1='.$paidSignature,
            'CONTENT_TYPE' => 'application/json',
        ], $paidBody);
        $paidResponse->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);

        $this->call('POST', '/webhooks/stripe', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$stamp.',v1='.$paidSignature,
            'CONTENT_TYPE' => 'application/json',
        ], $paidBody)->assertOk();
        $this->assertSame(1, PaymentEvent::query()->where('provider_event_id', 'evt_paid')->count());
    }

    public function test_paypal_webhook_is_rejected_unless_paypal_verifies_it(): void
    {
        StoreFixture::basics();
        config([
            'store.payments.paypal.client_id' => 'client',
            'store.payments.paypal.secret' => 'secret',
            'store.payments.paypal.webhook_id' => 'wh',
            'store.payments.paypal.base_url' => 'https://api-m.sandbox.paypal.com',
        ]);

        $order = Order::factory()->create(['payment_method' => 'paypal', 'payment_status' => 'authorized']);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'paypal',
            'status' => 'authorized',
            'amount' => $order->grand_total,
            'currency_code' => 'USD',
            'provider' => 'paypal',
            'provider_reference' => 'CAP123',
        ]);

        $paypalVerified = false;
        Http::fake(function ($request) use (&$paypalVerified) {
            $url = $request->url();

            if (str_contains($url, 'oauth2/token')) {
                return Http::response(['access_token' => 'tok']);
            }

            if (str_contains($url, 'verify-webhook-signature')) {
                return Http::response(['verification_status' => $paypalVerified ? 'SUCCESS' : 'FAILURE']);
            }

            if (str_contains($url, '/captures/')) {
                return Http::response(['id' => 'CAP123', 'status' => 'COMPLETED']);
            }

            return Http::response(['url' => $url], 500);
        });

        $this->postJson('/webhooks/paypal', [
            'id' => 'evt_pp',
            'resource' => ['id' => 'CAP123'],
        ], [
            'paypal-transmission-id' => 'tid',
            'paypal-transmission-time' => 'time',
            'paypal-cert-url' => 'https://example.test/cert',
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-transmission-sig' => 'sig',
        ])->assertStatus(400);
        $this->assertSame('authorized', $payment->fresh()->status);

        $paypalVerified = true;

        $paypalResponse = $this->postJson('/webhooks/paypal', [
            'id' => 'evt_pp',
            'resource' => ['id' => 'CAP123'],
        ], [
            'paypal-transmission-id' => 'tid',
            'paypal-transmission-time' => 'time',
            'paypal-cert-url' => 'https://example.test/cert',
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-transmission-sig' => 'sig',
        ]);
        $this->assertSame(200, $paypalResponse->status(), $paypalResponse->getContent());

        $this->assertSame('paid', $payment->fresh()->status);
    }
}
