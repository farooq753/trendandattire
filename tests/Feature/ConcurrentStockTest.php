<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\StoreFixture;
use Tests\TestCase;

class ConcurrentStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_purchases_cannot_both_take_the_last_unit(): void
    {
        StoreFixture::basics();
        $variant = StoreFixture::readyProduct(1, '50.0000')->variants->first();

        DB::commit();

        $results = [];

        $processes = [];

        foreach ([1, 2] as $ignored) {
            $pipes = [];
            $process = proc_open(
                [PHP_BINARY, base_path('tests/Support/concurrent_deduct.php'), (string) $variant->id],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
                base_path(),
            );
            $this->assertIsResource($process);
            $processes[] = [$process, $pipes];
        }

        foreach ($processes as [$process, $pipes]) {
            $results[] = trim((string) stream_get_contents($pipes[1]));
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }

        sort($results);
        $this->assertSame(['no', 'yes'], $results);
        $this->assertSame(0, ProductVariant::query()->find($variant->id)->stock_on_hand);

        $productId = $variant->product_id;
        Order::query()->where('customer_email', 'concurrent@test.local')->delete();
        ProductVariant::query()->whereKey($variant->id)->delete();
        Product::query()->whereKey($productId)->delete();
        DB::beginTransaction();
    }
}
