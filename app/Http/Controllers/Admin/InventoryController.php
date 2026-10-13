<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\AuditLogger;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $variants = ProductVariant::query()
            ->with(['product', 'attributeValues', 'movements' => fn ($query) => $query->latest()->limit(3)])
            ->when($request->boolean('low'), fn ($query) => $query->whereRaw('(stock_on_hand - reserved) <= (select low_stock_threshold from products where products.id = product_variants.product_id)'))
            ->orderBy('sku')
            ->paginate(30)
            ->withQueryString();

        return view('admin.inventory.index', ['variants' => $variants]);
    }

    public function adjust(Request $request, ProductVariant $variant, InventoryService $inventory, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'delta' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $updated = $inventory->adjust($variant, (int) $data['delta'], 'adjustment', $data['note'] ?? null);
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['delta' => $exception->getMessage()]);
        }

        $audit->log('inventory.adjusted', $updated, ['delta' => $data['delta']], ['stock_on_hand' => $updated->stock_on_hand]);

        return back()->with('status', 'Stock updated.');
    }
}
