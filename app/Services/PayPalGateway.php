<?php

namespace App\Services;

use App\Exceptions\InvalidWebhookSignature;
use App\Exceptions\PaymentGatewayDisabled;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;

class PayPalGateway
{
    public function __construct(private HttpFactory $http) {}

    public function enabled(): bool
    {
        return filled(config('store.payments.paypal.client_id'))
            && filled(config('store.payments.paypal.secret'))
            && filled(config('store.payments.paypal.webhook_id'));
    }

    /**
     * Ask PayPal to verify the webhook, then read the capture from PayPal.
     * The posted JSON is not treated as proof of payment by itself.
     *
     * @return array{event: array<string, mixed>, capture: array<string, mixed>}
     */
    public function verifiedCapture(Request $request): array
    {
        if (! $this->enabled()) {
            throw new PaymentGatewayDisabled('PayPal credentials are not configured.');
        }

        $event = json_decode($request->getContent(), true);

        if (! is_array($event)) {
            throw new InvalidWebhookSignature('PayPal webhook payload is not JSON.');
        }

        $token = $this->accessToken();
        $verification = $this->http
            ->withToken($token)
            ->acceptJson()
            ->timeout(10)
            ->post($this->base().'/v1/notifications/verify-webhook-signature', [
                'transmission_id' => $request->header('paypal-transmission-id'),
                'transmission_time' => $request->header('paypal-transmission-time'),
                'cert_url' => $request->header('paypal-cert-url'),
                'auth_algo' => $request->header('paypal-auth-algo'),
                'transmission_sig' => $request->header('paypal-transmission-sig'),
                'webhook_id' => config('store.payments.paypal.webhook_id'),
                'webhook_event' => $event,
            ]);

        if ($verification->json('verification_status') !== 'SUCCESS') {
            throw new InvalidWebhookSignature('PayPal did not verify this webhook.');
        }

        $captureId = $event['resource']['id'] ?? null;

        if (! is_string($captureId) || ! preg_match('/^[A-Za-z0-9]+$/', $captureId)) {
            throw new InvalidWebhookSignature('PayPal webhook is missing a capture id.');
        }

        $capture = $this->http
            ->withToken($token)
            ->acceptJson()
            ->timeout(10)
            ->get($this->base().'/v2/payments/captures/'.$captureId);

        if (! $capture->successful() || ! is_array($capture->json())) {
            throw new InvalidWebhookSignature('PayPal capture status could not be confirmed.');
        }

        return ['event' => $event, 'capture' => $capture->json()];
    }

    private function accessToken(): string
    {
        $response = $this->http
            ->withBasicAuth(
                (string) config('store.payments.paypal.client_id'),
                (string) config('store.payments.paypal.secret'),
            )
            ->asForm()
            ->timeout(10)
            ->post($this->base().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        $token = $response->json('access_token');

        if (! $response->successful() || ! is_string($token) || $token === '') {
            throw new InvalidWebhookSignature('PayPal access token could not be obtained.');
        }

        return $token;
    }

    private function base(): string
    {
        return rtrim((string) config('store.payments.paypal.base_url'), '/');
    }
}
