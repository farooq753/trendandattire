@extends('layouts.store')
@section('title', $title.' | Trend & Attire')
@section('content')
@include('components.breadcrumbs', ['items' => [$title => null]])
<div class="d-flex justify-content-between align-items-end mb-3">
    <h1 class="mb-0">{{ $title }}</h1>
    <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filters" aria-controls="filters">Filters</button>
</div>
<div class="row g-4">
    <div class="col-lg-3">
        <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="filters" aria-labelledby="filters-label">
            <div class="offcanvas-header">
                <h2 class="offcanvas-title h5" id="filters-label">Filters</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filters" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form method="GET">
                    @foreach (request()->except(['size', 'color', 'collar', 'fabric', 'fit', 'fulfillment', 'availability', 'min_price', 'max_price', 'sort', 'min_rating', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <div class="mb-3">
                        <label class="form-label" for="sort">Sort</label>
                        <select class="form-select" id="sort" name="sort">
                            @foreach (['newest' => 'Newest', 'price_asc' => 'Price, low to high', 'price_desc' => 'Price, high to low', 'best_selling' => 'Best selling', 'highest_rated' => 'Highest rated'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach ($attributes as $attribute)
                        <div class="mb-3">
                            <label class="form-label" for="filter-{{ $attribute->type }}">{{ $attribute->name }}</label>
                            <select class="form-select" id="filter-{{ $attribute->type }}" name="{{ $attribute->type }}">
                                <option value="">Any</option>
                                @foreach ($attribute->values as $value)
                                    <option value="{{ $value->slug }}" @selected(($filters[$attribute->type] ?? '') === $value->slug)>{{ $value->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="mb-3">
                        <label class="form-label" for="availability">Availability</label>
                        <select class="form-select" id="availability" name="availability">
                            <option value="">Any</option>
                            <option value="in_stock" @selected(($filters['availability'] ?? '') === 'in_stock')>In stock</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label" for="min_price">Min</label><input class="form-control" id="min_price" name="min_price" value="{{ $filters['min_price'] ?? '' }}" inputmode="decimal"></div>
                        <div class="col-6"><label class="form-label" for="max_price">Max</label><input class="form-control" id="max_price" name="max_price" value="{{ $filters['max_price'] ?? '' }}" inputmode="decimal"></div>
                    </div>
                    <button class="btn btn-navy mt-3" type="submit">Apply</button>
                    <a class="btn btn-link" href="{{ url()->current() }}">Clear all</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="row g-3">
            @forelse ($products as $product)
                <div class="col-6 col-xl-4">@include('components.product-card', ['product' => $product])</div>
            @empty
                <p>No garments match these filters.</p>
            @endforelse
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
        @if (($addons ?? collect())->isNotEmpty())
            <section class="shop-addons mt-5" aria-label="Add-ons">
                <h2 class="section-kicker">Add-ons</h2>
                <div class="row g-3">
                    @foreach ($addons as $addon)
                        <div class="col-6 col-xl-4">
                            <article class="product-card addon-card h-100">
                                <a href="{{ route('products.show', $addon) }}">
                                    <img src="{{ $addon->primaryImagePath() }}" alt="{{ $addon->images->first()->alt ?? $addon->name }}">
                                </a>
                                <div class="p-3">
                                    <p class="addon-kicker">Discounted price</p>
                                    <h3 class="h5"><a class="text-decoration-none" href="{{ route('products.show', $addon) }}">{{ $addon->name }}</a></h3>
                                    <p class="mb-1">@include('components.price', ['amount' => $addon->effectivePrice(), 'from' => $addon->currency_code])</p>
                                    <p class="text-secondary mb-0">{{ $addon->slug === 'scrub-cap' ? 'Standard size' : 'Sizes: S, M, L, XL' }}</p>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
