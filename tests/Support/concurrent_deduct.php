<?php

use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__.'/../../vendor/autoload.php';

$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$variant = ProductVariant::query()->findOrFail((int) ($argv[1] ?? 0));

$order = Order::query()->create([
    'number' => 'TA-RACE-'.Str::upper(Str::random(8)),
    'tracking_token' => bin2hex(random_bytes(32)),
    'checkout_token' => Str::random(40),
    'currency_code' => 'USD',
    'subtotal' => '50.0000',
    'grand_total' => '50.0000',
    'payment_method' => 'cod',
    'payment_status' => 'pending',
    'fulfillment_status' => 'unfulfilled',
    'customer_name' => 'Race',
    'customer_email' => 'concurrent@test.local',
    'customer_phone' => '+12025550123',
    'shipping_method_name' => 'Ground',
    'placed_at' => now(),
]);

$won = DB::transaction(fn () => app(InventoryService::class)->deduct($variant, 1, $order));

echo $won ? "yes\n" : "no\n";
