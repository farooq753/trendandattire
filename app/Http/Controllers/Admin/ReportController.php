<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->range($request);

        return view('admin.reports.index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'orders' => Order::query()->whereBetween('placed_at', [$from, $to])->count(),
            'paid' => Order::query()->whereBetween('placed_at', [$from, $to])->where('payment_status', 'paid')->sum('grand_total'),
            'lowStock' => $this->lowStock()->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $type = $request->string('type')->toString() ?: 'orders';
        [$from, $to] = $this->range($request);

        return response()->streamDownload(function () use ($type, $from, $to) {
            $handle = fopen('php://output', 'w');
            abort_unless($handle !== false, 500);

            if ($type === 'inventory') {
                fputcsv($handle, ['sku', 'product', 'on_hand', 'reserved', 'available']);
                $this->lowStock(false)->each(function (ProductVariant $variant) use ($handle) {
                    fputcsv($handle, [
                        $variant->sku,
                        $variant->product->name,
                        $variant->stock_on_hand,
                        $variant->reserved,
                        $variant->availableQuantity(),
                    ]);
                });
            } else {
                fputcsv($handle, ['number', 'placed_at', 'email', 'payment_status', 'fulfillment_status', 'currency', 'grand_total']);
                Order::query()->whereBetween('placed_at', [$from, $to])->orderBy('id')->each(function (Order $order) use ($handle) {
                    fputcsv($handle, [
                        $order->number,
                        $order->placed_at,
                        $order->customer_email,
                        $order->payment_status,
                        $order->fulfillment_status,
                        $order->currency_code,
                        $order->grand_total,
                    ]);
                });
            }

            fclose($handle);
        }, $type.'-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = ($request->date('from') ?? now()->startOfMonth())->startOfDay();
        $to = ($request->date('to') ?? now())->endOfDay();

        return [$from, $to];
    }

    private function lowStock(bool $onlyLow = true): Builder
    {
        return ProductVariant::query()
            ->with('product')
            ->when($onlyLow, fn ($query) => $query->whereRaw('(stock_on_hand - reserved) <= (select low_stock_threshold from products where products.id = product_variants.product_id)'));
    }
}
