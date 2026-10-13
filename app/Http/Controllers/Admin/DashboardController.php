<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductionJob;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        $orders = Order::query()->whereBetween('placed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        return view('admin.dashboard', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'orderCount' => (clone $orders)->count(),
            'paidTotal' => (clone $orders)->where('payment_status', 'paid')->sum('grand_total'),
            'pendingPayments' => (clone $orders)->whereIn('payment_status', ['pending', 'awaiting_payment', 'evidence_submitted'])->count(),
            'newCustomers' => User::query()->whereDoesntHave('roles')->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count(),
            'lowStock' => ProductVariant::query()->where('is_available', true)->whereRaw('(stock_on_hand - reserved) <= (select low_stock_threshold from products where products.id = product_variants.product_id)')->count(),
            'productionQueue' => ProductionJob::query()->whereNotIn('status', ['delivered', 'cancelled', 'dispatched'])->count(),
            'revenueByDay' => (clone $orders)->where('payment_status', 'paid')
                ->select(DB::raw('date(placed_at) as day'), DB::raw('sum(grand_total) as total'))
                ->groupBy('day')
                ->orderBy('day')
                ->get(),
        ]);
    }
}
