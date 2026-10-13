<?php

namespace Tests\Feature;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductionJob;
use App\Models\Shipment;
use App\Services\PaymentService;
use App\Services\ProductionWorkflow;
use App\Services\ReturnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class ProductionAndReturnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_status_changes_follow_the_allowed_path(): void
    {
        StoreFixture::basics();
        $order = Order::factory()->create();
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'name' => 'Custom stitched top',
            'sku' => 'TA-CUSTOM-TOP',
            'fulfillment_type' => 'custom',
            'quantity' => 1,
            'unit_price' => '92.0000',
            'line_total' => '92.0000',
            'production_status' => 'awaiting_review',
            'snapshot' => ['name' => 'Custom stitched top'],
        ]);
        $job = ProductionJob::query()->create([
            'order_item_id' => $item->id,
            'status' => 'awaiting_review',
            'lead_time_days' => 14,
        ]);

        $workflow = app(ProductionWorkflow::class);

        try {
            $workflow->transition($job, 'in_production');
            $this->fail('Skipped production states were accepted.');
        } catch (CheckoutException $exception) {
            $this->assertArrayHasKey('status', $exception->errors);
        }

        $workflow->transition($job->fresh(), 'measurements_review', 'Measurements look consistent.');
        $this->assertSame('measurements_review', $job->fresh()->status);
        $this->assertStringContainsString('Measurements look consistent.', (string) $job->fresh()->staff_notes);
    }

    public function test_returns_and_refunds_follow_their_state_machines(): void
    {
        StoreFixture::basics();
        $product = StoreFixture::readyProduct(4, '50.0000');
        $variant = $product->variants->first();
        $order = Order::factory()->create([
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'grand_total' => '50.0000',
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'name' => $product->name,
            'sku' => $variant->sku,
            'fulfillment_type' => 'ready',
            'quantity' => 1,
            'unit_price' => '50.0000',
            'line_total' => '50.0000',
            'snapshot' => ['name' => $product->name],
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'method' => 'cod',
            'status' => 'paid',
            'amount' => '50.0000',
            'currency_code' => 'USD',
        ]);
        $returns = app(ReturnService::class);

        try {
            $returns->request($order, null, 'Size', null, [['order_item_id' => $item->id, 'quantity' => 1]]);
            $this->fail('A return opened before shipment.');
        } catch (CheckoutException $exception) {
            $this->assertArrayHasKey('return', $exception->errors);
        }

        $order->fulfillment_status = 'fulfilled';
        $order->save();
        Shipment::query()->create([
            'order_id' => $order->id,
            'method_name' => 'Ground',
            'status' => 'shipped',
            'shipped_at' => now()->subDay(),
        ]);

        $return = $returns->request($order, null, 'Size', 'Too long.', [['order_item_id' => $item->id, 'quantity' => 1]]);
        $returns->transition($return, 'approved');
        $returns->transition($return->fresh(), 'received', null, true);
        $this->assertSame(5, $variant->fresh()->stock_on_hand);

        $payments = app(PaymentService::class);

        try {
            $payments->recordRefund($payment, '80.0000', 'Too much');
            $this->fail('An oversized refund was recorded.');
        } catch (CheckoutException $exception) {
            $this->assertArrayHasKey('refund', $exception->errors);
        }

        $payments->recordRefund($payment->fresh(), '20.0000', 'Partial goodwill');
        $this->assertSame('partially_refunded', $payment->fresh()->status);
    }
}
