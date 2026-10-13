<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AddressRequest;
use App\Models\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        return view('account.addresses', [
            'addresses' => auth()->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['is_default_shipping'] = $request->boolean('is_default_shipping');
        $data['is_default_billing'] = $request->boolean('is_default_billing');

        if ($data['is_default_shipping']) {
            $user->addresses()->update(['is_default_shipping' => false]);
        }

        if ($data['is_default_billing']) {
            $user->addresses()->update(['is_default_billing' => false]);
        }

        $user->addresses()->create($data);

        return back()->with('status', 'Address saved.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);
        $address->delete();

        return back()->with('status', 'Address removed.');
    }
}
