<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $carts,
        private CheckoutService $checkout,
        private PaymentService $payments,
        private StoreSettings $settings,
    ) {}

    public function show(Request $request): View
    {
        $token = session('checkout_token');

        if (! is_string($token) || strlen($token) !== 40) {
            $token = Str::random(40);
            session(['checkout_token' => $token]);
        }

        $cart = $this->carts->current($request->user(), session('guest_cart'));
        $quote = $this->checkout->quote($cart, [
            'currency_code' => session('currency_code'),
            'country_code' => old('shipping.country_code', session('country_code')),
            'region' => old('shipping.region'),
            'email' => $request->user()?->email,
            'shipping_method_id' => old('shipping_method_id'),
            'payment_method' => old('payment_method'),
        ]);

        return view('checkout.show', [
            'cart' => $cart,
            'quote' => $quote,
            'checkoutToken' => $token,
            'bank' => [
                'account_name' => $this->settings->get('bank_account_name'),
                'bank_name' => $this->settings->get('bank_name'),
                'account_number' => $this->settings->get('bank_account_number'),
                'swift' => $this->settings->get('bank_swift'),
                'instructions' => $this->settings->get('bank_instructions'),
            ],
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $cart = $this->carts->current($request->user(), session('guest_cart'));
        $payload = $request->validated();
        $payload['currency_code'] = session('currency_code');
        $payload['country_code'] = $payload['shipping']['country_code'];
        $payload['region'] = $payload['shipping']['region'] ?? null;
        $payload['billing_same'] = $request->boolean('billing_same', true);

        try {
            $order = $this->checkout->place($cart, $payload, $request->user());
        } catch (CheckoutException $exception) {
            return back()->withInput()->withErrors($exception->errors);
        }

        session()->forget('checkout_token');
        session(['confirmed_order' => $order->id, 'tracked.'.$order->tracking_token => true]);

        return redirect()->route('checkout.confirmation', $order);
    }

    public function confirmation(Request $request, Order $order): View
    {
        $this->authorizeView($request, $order);
        $order->load(['items.configurations', 'items.measurements', 'items.productionJob', 'addresses', 'payment', 'shipments']);

        return view('checkout.confirmation', [
            'order' => $order,
            'bank' => [
                'account_name' => $this->settings->get('bank_account_name'),
                'bank_name' => $this->settings->get('bank_name'),
                'account_number' => $this->settings->get('bank_account_number'),
                'swift' => $this->settings->get('bank_swift'),
                'instructions' => $this->settings->get('bank_instructions'),
            ],
        ]);
    }

    public function bankEvidence(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeView($request, $order);
        $data = $request->validate([
            'bank_reference' => ['required', 'string', 'max:80'],
            'customer_note' => ['nullable', 'string', 'max:500'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $path = $request->file('evidence')?->store('payment-evidence', 'private');

        try {
            $this->payments->submitBankEvidence($order->payment, $data['bank_reference'], $path, $data['customer_note'] ?? null);
        } catch (CheckoutException $exception) {
            if ($path) {
                Storage::disk('private')->delete($path);
            }

            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Reference received. The order stays unpaid until the store verifies the transfer.');
    }

    private function authorizeView(Request $request, Order $order): void
    {
        $owns = $request->user() && $request->user()->id === $order->user_id;
        $justPlaced = (int) session('confirmed_order') === $order->id;
        abort_unless($owns || $justPlaced, 403);
    }
}
