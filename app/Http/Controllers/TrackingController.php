<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackingController extends Controller
{
    public function create(): View
    {
        return view('tracking.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email'],
        ]);

        $order = Order::query()
            ->where('number', $data['number'])
            ->whereRaw('lower(customer_email) = ?', [strtolower($data['email'])])
            ->first();

        if (! $order) {
            return back()->withErrors(['number' => 'We could not match those details.'])->withInput();
        }

        session(['tracked.'.$order->tracking_token => true]);

        return redirect()->route('tracking.show', ['token' => $order->tracking_token]);
    }

    public function show(Request $request, string $token): View
    {
        $order = Order::query()->where('tracking_token', $token)->firstOrFail();
        $owns = $request->user() && $request->user()->id === $order->user_id;
        abort_unless($owns || session('tracked.'.$token) === true, 403);
        $order->load(['items.productionJob', 'shipments', 'events' => fn ($query) => $query->where('is_public', true)]);

        return view('tracking.show', ['order' => $order]);
    }
}
