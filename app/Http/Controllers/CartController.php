<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $carts,
        private CheckoutService $checkout,
    ) {}

    public function show(Request $request): View
    {
        $cart = $this->current($request);
        $quote = $this->checkout->quote($cart, [
            'currency_code' => session('currency_code'),
            'country_code' => session('country_code'),
            'email' => $request->user()?->email,
        ]);

        return view('cart.show', ['cart' => $cart, 'quote' => $quote]);
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $this->owns($request, $item);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        $this->carts->updateQuantity($item, (int) $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->owns($request, $item);
        $item->delete();

        return back()->with('status', 'Item removed.');
    }

    public function coupon(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['nullable', 'string', 'max:40']]);
        $this->carts->applyCoupon($this->current($request), $data['code'] ?? null);

        return back();
    }

    private function current(Request $request): Cart
    {
        return $this->carts->current($request->user(), session('guest_cart'));
    }

    private function owns(Request $request, CartItem $item): void
    {
        abort_unless($item->cart_id === $this->current($request)->id, 403);
    }
}
