<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(private StoreSettings $settings) {}

    public function available(ProductVariant $variant): int
    {
        return max(0, (int) $variant->stock_on_hand - (int) $variant->reserved);
    }

    public function adjust(ProductVariant $variant, int $delta, string $reason, ?string $note = null): ProductVariant
    {
        return DB::transaction(function () use ($variant, $delta, $reason, $note) {
            $locked = ProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();
            $next = (int) $locked->stock_on_hand + $delta;

            if ($next < (int) $locked->reserved) {
                throw new \RuntimeException('Stock cannot fall below the reserved quantity.');
            }

            $locked->stock_on_hand = $next;
            $locked->save();

            $this->movement($locked, $delta, 0, $reason, null, $note);

            return $locked->refresh();
        });
    }

    public function deduct(ProductVariant $variant, int $quantity, Order $order): bool
    {
        $locked = ProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();

        if ($this->available($locked) < $quantity) {
            return false;
        }

        $locked->stock_on_hand = (int) $locked->stock_on_hand - $quantity;
        $locked->save();
        $this->movement($locked, -$quantity, 0, 'sale', $order, 'Sold on '.$order->number);

        return true;
    }

    public function reserve(ProductVariant $variant, int $quantity, Order $order): bool
    {
        $locked = ProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();

        if ($this->available($locked) < $quantity) {
            return false;
        }

        $minutes = (int) $this->settings->get('reservation_minutes', 1440);
        $locked->reserved = (int) $locked->reserved + $quantity;
        $locked->save();

        StockReservation::query()->create([
            'product_variant_id' => $locked->id,
            'order_id' => $order->id,
            'quantity' => $quantity,
            'status' => 'active',
            'expires_at' => now()->addMinutes(max($minutes, 15)),
        ]);

        $this->movement($locked, 0, $quantity, 'reservation', $order, 'Reserved for '.$order->number);

        return true;
    }

    public function consumeReservations(Order $order): bool
    {
        $reservations = StockReservation::query()
            ->where('order_id', $order->id)
            ->where('status', 'active')
            ->lockForUpdate()
            ->get();

        foreach ($reservations as $reservation) {
            $variant = ProductVariant::query()->whereKey($reservation->product_variant_id)->lockForUpdate()->firstOrFail();

            if ((int) $variant->stock_on_hand < $reservation->quantity || (int) $variant->reserved < $reservation->quantity) {
                return false;
            }

            $variant->stock_on_hand = (int) $variant->stock_on_hand - $reservation->quantity;
            $variant->reserved = (int) $variant->reserved - $reservation->quantity;
            $variant->save();
            $reservation->status = 'consumed';
            $reservation->save();
            $this->movement($variant, -$reservation->quantity, -$reservation->quantity, 'sale', $order, 'Reservation consumed');
        }

        return true;
    }

    public function releaseOrderReservations(Order $order, string $status = 'released'): void
    {
        $reservations = StockReservation::query()
            ->where('order_id', $order->id)
            ->where('status', 'active')
            ->lockForUpdate()
            ->get();

        foreach ($reservations as $reservation) {
            $this->releaseReservation($reservation, $status);
        }
    }

    public function restockSale(ProductVariant $variant, int $quantity, Order $order, string $note): void
    {
        $locked = ProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail();
        $locked->stock_on_hand = (int) $locked->stock_on_hand + $quantity;
        $locked->save();
        $this->movement($locked, $quantity, 0, 'return', $order, $note);
    }

    public function releaseExpired(): int
    {
        $count = 0;

        StockReservation::query()
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->orderBy('id')
            ->each(function (StockReservation $reservation) use (&$count) {
                DB::transaction(function () use ($reservation) {
                    $locked = StockReservation::query()->whereKey($reservation->id)->lockForUpdate()->first();

                    if (! $locked || $locked->status !== 'active') {
                        return;
                    }

                    $this->releaseReservation($locked, 'expired');
                });
                $count++;
            });

        return $count;
    }

    private function releaseReservation(StockReservation $reservation, string $status): void
    {
        $variant = ProductVariant::query()->whereKey($reservation->product_variant_id)->lockForUpdate()->first();

        if ($variant) {
            $variant->reserved = max(0, (int) $variant->reserved - $reservation->quantity);
            $variant->save();
            $this->movement($variant, 0, -$reservation->quantity, 'release', $reservation->order, 'Reservation '.$status);
        }

        $reservation->status = $status;
        $reservation->save();
    }

    private function movement(ProductVariant $variant, int $quantityDelta, int $reservedDelta, string $reason, ?Order $order, ?string $note): void
    {
        InventoryMovement::query()->create([
            'product_variant_id' => $variant->id,
            'quantity_delta' => $quantityDelta,
            'reserved_delta' => $reservedDelta,
            'reason' => $reason,
            'reference_type' => $order?->getMorphClass(),
            'reference_id' => $order?->id,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);
    }
}
