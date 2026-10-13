<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $wishlist = auth()->user()->wishlist()->firstOrCreate();

        return view('account.wishlist', [
            'items' => $wishlist->items()->with(['product.images', 'variant'])->latest()->get(),
        ]);
    }

    public function store(Product $product): RedirectResponse
    {
        abort_unless($product->is_published, 404);
        $wishlist = auth()->user()->wishlist()->firstOrCreate();
        $wishlist->items()->firstOrCreate([
            'product_id' => $product->id,
            'product_variant_id' => null,
        ]);

        return back()->with('status', 'Saved to your wishlist.');
    }

    public function destroy(WishlistItem $item): RedirectResponse
    {
        abort_unless($item->wishlist->user_id === auth()->id(), 403);
        $item->delete();

        return back()->with('status', 'Removed from your wishlist.');
    }
}
