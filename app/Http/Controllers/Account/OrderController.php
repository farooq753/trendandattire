<?php

namespace App\Http\Controllers\Account;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Services\InventoryService;
use App\Services\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('account.orders.index', [
            'orders' => auth()->user()->orders()->latest('placed_at')->paginate(10),
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);
        $order->load(['items.configurations', 'items.measurements', 'items.productionJob', 'addresses', 'payment', 'shipments.items', 'events' => fn ($query) => $query->where('is_public', true)]);

        return view('account.orders.show', [
            'order' => $order,
            'showMeasurements' => auth()->user()->measurement_history_consent_at !== null,
        ]);
    }

    public function cancel(Order $order, InventoryService $inventory, StoreSettings $settings): RedirectResponse
    {
        Gate::authorize('view', $order);
        $hours = (int) $settings->get('cancellation_window_hours', 12);
        $open = in_array($order->payment_status, [PaymentStatus::Pending->value, PaymentStatus::AwaitingPayment->value, PaymentStatus::EvidenceSubmitted->value], true)
            && $order->fulfillment_status === FulfillmentStatus::Unfulfilled->value
            && $order->placed_at->gt(now()->subHours($hours))
            && $order->items()->whereHas('productionJob', fn ($query) => $query->whereNotIn('status', ['awaiting_review', 'measurements_review']))->doesntExist();

        abort_unless($open, 403);

        DB::transaction(function () use ($order, $inventory) {
            if ($order->payment_method === 'bank_transfer') {
                $inventory->releaseOrderReservations($order);
            }

            if ($order->payment_method === 'cod') {
                foreach ($order->items()->where('fulfillment_type', 'ready')->get() as $item) {
                    if ($item->variant) {
                        $inventory->restockSale($item->variant, $item->quantity, $order, 'Customer cancellation');
                    }
                }
            }

            $order->payment_status = PaymentStatus::Cancelled->value;
            $order->fulfillment_status = FulfillmentStatus::Cancelled->value;
            $order->cancelled_at = now();
            $order->save();
            $order->payment?->update(['status' => PaymentStatus::Cancelled->value]);
            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'cancelled',
                'message' => 'Order cancelled by the customer.',
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('status', 'Order cancelled.');
    }
}
