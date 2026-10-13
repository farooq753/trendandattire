<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Services\CartService;
use App\Services\CurrencyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetStorefrontContext
{
    public function __construct(private CurrencyService $currencies) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $request->session()->has('locale')) {
            $request->session()->put('locale', $user->locale ?? config('app.locale', 'en'));
        }

        if (! $request->session()->has('currency_code')) {
            $request->session()->put('currency_code', $user->currency_code ?? $this->currencies->defaultCode());
        }

        if (! $request->session()->has('country_code') && $user?->country_code) {
            $request->session()->put('country_code', $user->country_code);
        }

        app()->setLocale((string) $request->session()->get('locale', 'en'));

        if (! $user) {
            $token = $request->session()->get('guest_cart', $request->cookie('guest_cart'));

            if (! is_string($token) || $token === '') {
                $token = (string) Str::uuid();
                cookie()->queue(cookie('guest_cart', $token, 60 * 24 * 30, httpOnly: true, sameSite: 'lax'));
            }

            $request->session()->put('guest_cart', $token);
        }

        $cart = app(CartService::class)->current($user, $request->session()->get('guest_cart'));

        View::share([
            'navCategories' => Category::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
            'storeCurrencies' => Currency::query()->where('is_active', true)->orderBy('code')->get(),
            'storeCountries' => Country::query()->where('is_active', true)->orderBy('name')->get(),
            'cartCount' => (int) $cart->items()->sum('quantity'),
            'activeCurrency' => $request->session()->get('currency_code'),
        ]);

        return $next($request);
    }
}
