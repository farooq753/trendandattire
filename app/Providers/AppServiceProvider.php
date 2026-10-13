<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Services\CartService;
use App\Services\CurrencyService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('password', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('tracking', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        View::composer('layouts.store', function ($view) {
            $cart = app(CartService::class)->current(auth()->user(), session('guest_cart'));
            $view->with([
                'navCategories' => Category::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
                'storeCurrencies' => Currency::query()->where('is_active', true)->orderBy('code')->get(),
                'storeCountries' => Country::query()->where('is_active', true)->orderBy('name')->get(),
                'cartCount' => (int) $cart->items()->sum('quantity'),
                'activeCurrency' => session('currency_code', app(CurrencyService::class)->defaultCode()),
            ]);
        });
    }
}
