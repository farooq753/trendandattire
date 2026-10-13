<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\ProductReturn;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(
        private StoreSettings $settings,
        private AuditLogger $audit,
        private InventoryService $inventory,
    ) {}

    /**
     * @param  array<int, array{order_item_id: int, quantity: int}>  $items
     */
    public function request(Order $order, ?User $user, string $reason, ?string $details, array $items): ProductReturn
    {
        $window = (int) $this->settings->get('return_window_days', 30);

        if ($order->cancelled_at || ! in_array($order->fulfillment_status, ['partial', 'fulfilled'], true)) {
            throw new CheckoutException(['return' => 'Returns open after the order has shipped.']);
        }

        $shippedAt = $order->shipments()->whereNotNull('shipped_at')->latest('shipped_at')->value('shipped_at');

        if ($shippedAt && abs(now()->diffInDays($shippedAt)) > $window) {
            throw new CheckoutException(['return' => 'The return window for this order has closed.']);
        }

        return DB::transaction(function () use ($order, $user, $reason, $details, $items) {
            $return = ProductReturn::query()->create([
                'order_id' => $order->id,
                'user_id' => $user?->id,
                'status' => 'requested',
                'reason' => $reason,
                'details' => $details,
            ]);

            foreach ($items as $item) {
                $line = $order->items()->whereKey($item['order_item_id'])->first();

                if (! $line || $item['quantity'] < 1 || $item['quantity'] > $line->quantity) {
                    throw new CheckoutException(['items' => 'Choose a valid item and quantity to return.']);
                }

                $return->items()->create([
                    'order_item_id' => $line->id,
                    'quantity' => $item['quantity'],
                ]);
            }

            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'return_requested',
                'message' => 'Return requested.',
                'user_id' => $user?->id,
            ]);

            return $return;
        });
    }

    public function transition(ProductReturn $return, string $status, ?string $notes = null, bool $restock = false): void
    {
        $allowed = [
            'requested' => ['approved', 'rejected'],
            'approved' => ['received', 'rejected'],
            'received' => ['refunded', 'closed'],
            'refunded' => ['closed'],
            'rejected' => [],
            'closed' => [],
        ];

        if (! in_array($status, $allowed[$return->status] ?? [], true)) {
            throw new CheckoutException(['status' => 'That return status change is not allowed.']);
        }

        DB::transaction(function () use ($return, $status, $notes, $restock) {
            $before = $return->status;
            $return->status = $status;
            $return->staff_notes = $notes;
            $return->save();

            if ($status === 'received' && $restock) {
                $return->load('items.orderItem.variant');

                foreach ($return->items as $item) {
                    $variant = $item->orderItem->variant;

                    if ($variant && $item->orderItem->fulfillment_type === 'ready') {
                        $this->inventory->restockSale($variant, $item->quantity, $return->order, 'Return received');
                    }
                }
            }

            $this->audit->log('return.'.$status, $return, ['status' => $before], ['status' => $status]);
            OrderEvent::query()->create([
                'order_id' => $return->order_id,
                'type' => 'return_'.$status,
                'message' => 'Return '.$status.'.',
                'user_id' => auth()->id(),
            ]);
        });
    }
}
