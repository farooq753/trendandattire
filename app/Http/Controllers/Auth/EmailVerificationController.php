<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\StoreSettings;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(StoreSettings $settings): View|RedirectResponse
    {
        if (! $settings->get('require_email_verification', false) || request()->user()->hasVerifiedEmail()) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.verify-email');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->id === $id && hash_equals(sha1($user->getEmailForVerification()), $hash), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('account.dashboard')->with('status', 'Email verified.');
    }

    public function send(Request $request, StoreSettings $settings): RedirectResponse
    {
        abort_unless($settings->get('require_email_verification', false), 404);

        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('status', 'Verification link sent.');
    }
}
