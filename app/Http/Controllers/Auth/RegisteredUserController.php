<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\CartService;
use App\Services\StoreSettings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, CartService $carts, StoreSettings $settings): RedirectResponse
    {
        $user = User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->lower()->toString(),
            'password' => $request->string('password')->toString(),
            'phone' => $request->string('phone')->toString(),
            'locale' => session('locale', 'en'),
            'currency_code' => session('currency_code', 'USD'),
            'country_code' => session('country_code'),
        ]);

        event(new Registered($user));
        Auth::login($user);
        $carts->merge(session('guest_cart'), $user);
        session()->forget('guest_cart');

        if ($settings->get('require_email_verification', false)) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice');
        }

        return redirect()->route('account.dashboard');
    }
}
