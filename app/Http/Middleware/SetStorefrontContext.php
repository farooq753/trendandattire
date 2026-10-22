<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Services\CartService;
use App\Services\CurrencyService;
use App\Support\PublicPath;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetStorefrontContext
{
    public function __construct(private CurrencyService $currencies) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->useInstalledPublicDirectory($request);

        $user = $request->user();

        if (! $request->session()->has('locale')) {
            $request->session()->put('locale', $user->locale ?? config('app.locale', 'en'));
        }

        $currency = $request->session()->get('currency_code');

        if (! is_string($currency) || ! $this->currencies->canCheckoutIn($currency)) {
            $preferred = $user->currency_code ?? $this->currencies->defaultCode();

            if (! is_string($preferred) || ! $this->currencies->canCheckoutIn($preferred)) {
                $preferred = $this->currencies->defaultCode();
            }

            $request->session()->put('currency_code', $preferred);
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

    private function useInstalledPublicDirectory(Request $request): void
    {
        $base = $this->installedPublicBase($request);

        URL::forceRootUrl($base === '' ? null : $request->getSchemeAndHttpHost().$base);
    }

    /**
     * Folder between Apache's document root and public/, such as /trendandattire/public.
     * An empty document root must stay empty: realpath('') is the process working directory.
     */
    private function installedPublicBase(Request $request): string
    {
        $documentRoot = trim((string) $request->server->get('DOCUMENT_ROOT', ''));

        if ($documentRoot === '') {
            return '';
        }

        $resolvedRoot = realpath($documentRoot);
        $publicPath = realpath(public_path());

        if ($resolvedRoot === false || $publicPath === false) {
            return '';
        }

        return PublicPath::baseFromRoots($resolvedRoot, $publicPath);
    }
}
