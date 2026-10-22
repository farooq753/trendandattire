<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function show(Product $product): View
    {
        abort_unless($product->is_published && $product->acceptsReadyMade(), 404);
        $product->load(['images', 'category', 'variants.attributeValues.attribute', 'fabrics', 'customizationOptions.values', 'measurementSchemas']);
        $reviews = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->latest()
            ->limit(10)
            ->get();

        return view('products.show', [
            'product' => $product,
            'reviews' => $reviews,
            'ratingAverage' => $reviews->avg('rating'),
            'ratingCount' => ProductReview::query()->where('product_id', $product->id)->where('is_approved', true)->count(),
        ]);
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_published && $product->acceptsReadyMade(), 404);

        $data = $request->validate([
            'variant_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->whereKey($data['variant_id'])
            ->where('is_available', true)
            ->first();

        if (! $variant) {
            return back()->withErrors(['variant_id' => 'That combination is unavailable.']);
        }

        $cart = $this->carts->current($request->user(), session('guest_cart'));
        $this->carts->add(
            $cart,
            $product,
            $variant,
            'ready',
            (int) $data['quantity'],
            $this->carts->readyPrice($product, $variant),
        );

        return redirect()->route('cart.show')->with('status', 'Added to cart.');
    }
}
