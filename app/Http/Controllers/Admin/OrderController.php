<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FulfillmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Shipment;
use App\Services\AuditLogger;
use App\Services\InventoryService;
use App\Services\OrderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->when($request->filled('status'), fn ($query) => $query->where('payment_status', $request->string('status')))
            ->latest('placed_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.configurations', 'items.measurements', 'items.productionJob', 'items.shipmentItems', 'addresses', 'payment.events', 'shipments.items', 'events', 'refunds', 'returns']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function invoice(Order $order): View
    {
        $order->load(['items', 'addresses', 'payment']);

        return view('admin.orders.invoice', ['order' => $order]);
    }

    public function packingSlip(Order $order): View
    {
        $order->load(['items.configurations', 'addresses']);

        return view('admin.orders.packing-slip', ['order' => $order]);
    }

    public function ship(Request $request, Order $order, OrderNotifier $notifier, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'carrier' => ['nullable', 'string', 'max:40'],
            'tracking_number' => ['nullable', 'string', 'max:80'],
            'tracking_url' => ['nullable', 'url', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($order, $data, $audit) {
            $shipment = Shipment::query()->create([
                'order_id' => $order->id,
                'method_name' => $order->shipping_method_name,
                'carrier' => $data['carrier'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'tracking_url' => $data['tracking_url'] ?? null,
                'status' => 'shipped',
                'shipped_at' => now(),
                'notes' => 'Manual shipment. Live carrier rates are not used.',
            ]);

            foreach ($data['items'] as $row) {
                $item = $order->items()->whereKey($row['order_item_id'])->firstOrFail();
                $remaining = $item->quantity - $item->shippedQuantity();
                abort_if($row['quantity'] > $remaining, 422, 'Shipment quantity exceeds the remaining quantity.');
                $shipment->items()->create([
                    'order_item_id' => $item->id,
                    'quantity' => $row['quantity'],
                ]);
            }

            $order->load('items.shipmentItems');
            $complete = $order->items->every(fn ($item) => $item->shipmentItems->sum('quantity') >= $item->quantity);
            $order->fulfillment_status = $complete ? FulfillmentStatus::Fulfilled->value : FulfillmentStatus::Partial->value;
            $order->save();
            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'shipped',
                'message' => 'Shipment recorded'.($data['tracking_number'] ? ' ('.$data['tracking_number'].')' : '').'.',
                'user_id' => auth()->id(),
            ]);
            $audit->log('order.shipped', $order, null, ['fulfillment_status' => $order->fulfillment_status]);
        });

        $notifier->status($order->fresh(), 'A shipment was recorded for your order.');

        return back()->with('status', 'Shipment saved.');
    }

    public function cancel(Order $order, InventoryService $inventory, AuditLogger $audit): RedirectResponse
    {
        abort_if($order->fulfillment_status === FulfillmentStatus::Fulfilled->value, 422);

        DB::transaction(function () use ($order, $inventory, $audit) {
            $inventory->releaseOrderReservations($order);
            $order->fulfillment_status = FulfillmentStatus::Cancelled->value;
            $order->cancelled_at = now();
            $order->save();
            $audit->log('order.cancelled', $order);
            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'cancelled',
                'message' => 'Order cancelled by staff.',
                'is_public' => true,
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('status', 'Order cancelled.');
    }
}
