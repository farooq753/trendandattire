<?php

namespace App\Http\Controllers\Webhook;

use App\Exceptions\InvalidWebhookSignature;
use App\Exceptions\PaymentGatewayDisabled;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentWebhookController extends Controller
{
    public function stripe(Request $request, PaymentService $payments): Response
    {
        return $this->handle(fn () => $payments->handleStripe($request));
    }

    public function paypal(Request $request, PaymentService $payments): Response
    {
        return $this->handle(fn () => $payments->handlePayPal($request));
    }

    private function handle(callable $callback): Response
    {
        try {
            $callback();
        } catch (PaymentGatewayDisabled $exception) {
            return response($exception->getMessage(), 503);
        } catch (InvalidWebhookSignature $exception) {
            return response($exception->getMessage(), 400);
        }

        return response('ok');
    }
}
