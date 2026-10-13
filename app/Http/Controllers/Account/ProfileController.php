<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('account.profile', ['user' => auth()->user()]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['measurement_history_consent']);
        $data['measurement_history_consent_at'] = $request->boolean('measurement_history_consent')
            ? ($user->measurement_history_consent_at ?? now())
            : null;
        $user->update($data);
        session([
            'locale' => $user->locale,
            'currency_code' => $user->currency_code,
            'country_code' => $user->country_code,
        ]);

        return back()->with('status', 'Profile saved.');
    }

    public function destroyRequest(): RedirectResponse
    {
        $user = auth()->user();
        $user->deletion_requested_at = now();
        $user->save();

        return back()->with('status', 'Deletion requested. The account stays available until retention review is complete.');
    }
}
