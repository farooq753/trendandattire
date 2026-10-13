<?php

namespace App\Services;

use App\Exceptions\InvalidWebhookSignature;
use App\Exceptions\PaymentGatewayDisabled;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;

class StripeGateway
{
    public function __construct(private HttpFactory $http) {}

    public function enabled(): bool
    {
        return filled(config('store.payments.stripe.secret'))
            && filled(config('store.payments.stripe.webhook_secret'));
    }

    /**
     * @return array<string, mixed>
     */
    public function verifiedEvent(Request $request): array
    {
        if (! $this->enabled()) {
            throw new PaymentGatewayDisabled('Stripe credentials are not configured.');
        }

        $secret = (string) config('store.payments.stripe.webhook_secret');
        $parts = [];

        foreach (explode(',', (string) $request->header('Stripe-Signature')) as $piece) {
            [$key, $value] = array_pad(explode('=', trim($piece), 2), 2, null);
            if ($key) {
                $parts[$key][] = $value;
            }
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if (! $timestamp || abs(time() - (int) $timestamp) > 300) {
            throw new InvalidWebhookSignature('Stripe webhook timestamp is outside the allowed window.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
        $valid = false;

        foreach ($signatures as $signature) {
            if (is_string($signature) && hash_equals($expected, $signature)) {
                $valid = true;
            }
        }

        if (! $valid) {
            throw new InvalidWebhookSignature('Stripe webhook signature does not match.');
        }

        $decoded = json_decode($request->getContent(), true);

        if (! is_array($decoded)) {
            throw new InvalidWebhookSignature('Stripe webhook payload is not JSON.');
        }

        return $decoded;
    }

    /**
     * Confirm the PaymentIntent with Stripe. A webhook body alone is not enough.
     *
     * @return array<string, mixed>|null
     */
    public function retrievePaymentIntent(string $intentId): ?array
    {
        if (! $this->enabled() || ! preg_match('/^pi_[A-Za-z0-9]+$/', $intentId)) {
            return null;
        }

        $response = $this->http
            ->withToken((string) config('store.payments.stripe.secret'))
            ->acceptJson()
            ->timeout(10)
            ->get('https://api.stripe.com/v1/payment_intents/'.$intentId);

        if (! $response->successful() || ! is_array($response->json())) {
            return null;
        }

        return $response->json();
    }
}
