<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class CatalogQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Product>
     */
    public function listing(array $filters): Builder
    {
        $query = Product::query()
            ->published()
            ->whereIn('fulfillment_type', ['ready', 'both'])
            ->with(['images', 'category', 'variants.attributeValues.attribute']);

        if ($gender = $filters['gender'] ?? null) {
            $query->where('gender', $gender);
        }

        if ($garment = $filters['garment_type'] ?? null) {
            $query->where('garment_type', $garment);
        }

        if ($category = $filters['category'] ?? null) {
            $query->whereHas('category', fn (Builder $categories) => $categories->where('slug', $category));
        }

        if (! empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (! empty($filters['sale'])) {
            $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'regular_price');
        }

        if (! empty($filters['new'])) {
            $query->where('published_at', '>=', now()->subDays(45));
        }

        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function (Builder $inner) use ($like) {
                $inner->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('keywords', 'like', $like)
                    ->orWhere('short_description', 'like', $like);
            });
        }

        foreach (['size', 'color', 'collar', 'fabric', 'fit'] as $type) {
            $value = $filters[$type] ?? null;

            if (! $value) {
                continue;
            }

            $query->where(function (Builder $inner) use ($type, $value) {
                $inner->whereHas('variants.attributeValues', function (Builder $values) use ($type, $value) {
                    $values->where('slug', $value)
                        ->whereHas('attribute', fn (Builder $attributes) => $attributes->where('type', $type));
                });

                if (in_array($type, ['color', 'collar', 'fit', 'fabric'], true)) {
                    $inner->orWhereHas('customizationOptions.values', function (Builder $values) use ($type, $value) {
                        $values->where('value', $value)->whereHas('option', fn (Builder $options) => $options->where('code', $type));
                    });
                }
            });
        }

        if (($filters['min_price'] ?? '') !== '') {
            $query->whereRaw('COALESCE(sale_price, regular_price) >= ?', [(string) $filters['min_price']]);
        }

        if (($filters['max_price'] ?? '') !== '') {
            $query->whereRaw('COALESCE(sale_price, regular_price) <= ?', [(string) $filters['max_price']]);
        }

        if (($filters['availability'] ?? null) === 'in_stock') {
            $query->whereHas('variants', function (Builder $variants) {
                $variants->where('is_available', true)->whereColumn('stock_on_hand', '>', 'reserved');
            });
        }

        if (($filters['min_rating'] ?? '') !== '') {
            $minimum = (float) $filters['min_rating'];
            $query->whereIn('products.id', function ($sub) use ($minimum) {
                $sub->select('product_id')
                    ->from('product_reviews')
                    ->where('is_approved', true)
                    ->groupBy('product_id')
                    ->havingRaw('avg(rating) >= ?', [$minimum]);
            });
        }

        return match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, regular_price) asc')->orderBy('products.id'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, regular_price) desc')->orderByDesc('products.id'),
            'best_selling' => $query
                ->withSum('orderItems as units_sold', 'quantity')
                ->orderByDesc('units_sold')
                ->orderByDesc('products.id'),
            'highest_rated' => $query
                ->withAvg(['reviews as approved_rating' => fn (Builder $reviews) => $reviews->where('is_approved', true)], 'rating')
                ->orderByDesc('approved_rating')
                ->orderByDesc('products.id'),
            default => $query->orderByDesc('published_at')->orderByDesc('products.id'),
        };
    }
}
