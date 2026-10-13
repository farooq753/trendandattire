<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReturnController extends Controller
{
    public function store(Request $request, Order $order, ReturnService $returns): RedirectResponse
    {
        Gate::authorize('view', $order);
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:180'],
            'details' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $returns->request($order, $request->user(), $data['reason'], $data['details'] ?? null, $data['items']);
        } catch (CheckoutException $exception) {
            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Return requested.');
    }
}
