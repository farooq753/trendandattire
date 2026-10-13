<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_adjust_deduct_reserve_and_release(): void
    {
        StoreFixture::basics();
        $product = StoreFixture::readyProduct(4);
        $variant = $product->variants->first();
        $inventory = app(InventoryService::class);

        $inventory->adjust($variant, 2, 'receipt', 'Received');
        $this->assertSame(6, $variant->fresh()->stock_on_hand);

        $this->expectException(\RuntimeException::class);
        $inventory->adjust($variant->fresh(), -100, 'correction');
    }

    public function test_reservation_holds_stock_until_expiry(): void
    {
        StoreFixture::basics();
        $variant = StoreFixture::readyProduct(2)->variants->first();
        $order = Order::factory()->create();
        $inventory = app(InventoryService::class);

        $this->assertTrue($inventory->reserve($variant, 2, $order));
        $this->assertSame(0, $inventory->available($variant->fresh()));
        $this->assertFalse($inventory->deduct($variant->fresh(), 1, Order::factory()->create()));

        $order->reservations()->update(['expires_at' => now()->subMinute()]);
        $this->assertSame(1, $inventory->releaseExpired());
        $this->assertSame(2, $inventory->available($variant->fresh()));
    }
}
