<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $item = $request->user()->orders()
            ->where('payment_status', 'paid')
            ->whereHas('items', fn ($query) => $query->whereKey($data['order_item_id'])->where('product_id', $product->id))
            ->exists();

        abort_unless($item, 403);

        ProductReview::query()->updateOrCreate(
            ['user_id' => $request->user()->id, 'order_item_id' => $data['order_item_id']],
            [
                'product_id' => $product->id,
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'is_approved' => false,
            ],
        );

        return back()->with('status', 'Review submitted for moderation.');
    }

    public function approve(ProductReview $review): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('reviews.moderate'), 403);
        $review->is_approved = true;
        $review->save();

        return back()->with('status', 'Review approved.');
    }
}
