@extends('layouts.store')
@section('title', ($product->meta_title ?: $product->name).' | Trend & Attire')
@section('description', $product->meta_description ?: $product->short_description)
@section('content')
@include('components.breadcrumbs', ['items' => ['Shop' => route('shop'), $product->name => null]])
@php
    $recolor = $product->garment_type === 'set';
    $requestedColour = (string) request('color', 'black');
@endphp
<div class="row g-4 product-gallery" @if($recolor) data-color-root="{{ asset('images/products/colors') }}" @endif>
    <div class="col-lg-6">
        <img id="product-photo" class="img-fluid placeholder-art" @if($recolor) data-recolor data-file="{{ basename($product->images->firstWhere('is_primary', true)->path ?? $product->images->first()->path) }}" data-original="{{ $product->primaryImagePath() }}" @endif src="{{ $product->primaryImagePath() }}" alt="{{ $product->images->first()->alt ?? $product->name }}">
        <div class="d-flex gap-2 mt-2 flex-wrap">
            @foreach ($product->images as $image)
                <button class="thumb-btn" type="button" data-file="{{ basename($image->path) }}" data-original="{{ $image->url() }}">
                    <img @if($recolor) data-recolor data-file="{{ basename($image->path) }}" data-original="{{ $image->url() }}" @endif src="{{ $image->url() }}" alt="{{ $image->alt }}" width="72" height="90">
                </button>
            @endforeach
        </div>
    </div>
    <div class="col-lg-6">
        <p class="text-secondary text-uppercase">{{ $product->gender }} · {{ $product->garment_type }}</p>
        <h1>{{ $product->name }}</h1>
        <p class="fs-4">@include('components.price', ['amount' => $product->effectivePrice(), 'from' => $product->currency_code])</p>
        @php
            $colours = $product->variants
                ->flatMap(fn ($variant) => $variant->attributeValues)
                ->filter(fn ($value) => $value->attribute?->type === 'color')
                ->unique('id')
                ->sortBy('sort_order');
            if ($colours->isNotEmpty() && ! $colours->contains(fn ($colour) => $colour->slug === $requestedColour)) {
                $requestedColour = $colours->first()->slug;
            }
        @endphp
        @if ($colours->isNotEmpty())
            <div class="color-swatches" aria-label="Available colours">
                @foreach ($colours as $colour)
                    <button class="color-swatch @if($colour->slug === $requestedColour) is-selected @endif" type="button" data-color="{{ $colour->slug }}" style="background: {{ $colour->meta['hex'] ?? '#cccccc' }}" aria-label="{{ $colour->value }}" aria-pressed="{{ $colour->slug === $requestedColour ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <p class="text-secondary">{{ $colours->pluck('value')->implode(', ') }}</p>
        @endif
        @if ($product->short_description)<p>{{ $product->short_description }}</p>@endif
        <dl class="row">
            <dt class="col-4">SKU</dt><dd class="col-8">{{ $product->sku }}</dd>
            @if ($product->fabric_composition)<dt class="col-4">Fabric</dt><dd class="col-8">{{ $product->fabric_composition }}</dd>@endif
            @if ($product->gsm)<dt class="col-4">GSM</dt><dd class="col-8">{{ $product->gsm }}</dd>@endif
            @if ($product->stretch)<dt class="col-4">Stretch</dt><dd class="col-8">{{ $product->stretch }}</dd>@endif
            @if ($product->fit)<dt class="col-4">Fit</dt><dd class="col-8">{{ $product->fit }}</dd>@endif
            @if ($product->care)<dt class="col-4">Care</dt><dd class="col-8">{{ $product->care }}</dd>@endif
            @if ($product->origin_country)<dt class="col-4">Origin</dt><dd class="col-8">{{ $product->origin_country }}</dd>@endif
            @if ($product->production_lead_days)<dt class="col-4">Lead time</dt><dd class="col-8">{{ $product->production_lead_days }} production days, before shipping.</dd>@endif
        </dl>
        @if ($product->acceptsReadyMade() && $product->variants->isNotEmpty())
            <form method="POST" action="{{ route('products.cart', $product) }}">
                @csrf
                <label class="form-label" for="variant_id">Size and color</label>
                <select class="form-select mb-3" id="variant_id" name="variant_id" required>
                    @foreach ($product->variants as $variant)
                        @php
                            $variantColour = $variant->attributeValues->first(fn ($value) => $value->attribute?->type === 'color')?->slug;
                            $variantSize = $variant->attributeValues->first(fn ($value) => $value->attribute?->type === 'size')?->slug;
                        @endphp
                        <option value="{{ $variant->id }}" data-color="{{ $variantColour }}" data-size="{{ $variantSize }}" @selected($variantColour === $requestedColour && $variantSize === 'm') @disabled(! $variant->is_available || ($product->track_inventory && $variant->availableQuantity() < 1))>
                            {{ $variant->label() }} — {{ $variant->availableQuantity() }} available
                        </option>
                    @endforeach
                </select>
                <label class="form-label" for="quantity">Quantity</label>
                <input class="form-control mb-3" id="quantity" name="quantity" type="number" min="1" max="20" value="1">
                <x-button>Add to cart</x-button>
            </form>
        @endif
        @auth
            <form class="mt-3" method="POST" action="{{ route('wishlist.store', $product) }}">@csrf<button class="btn btn-outline-secondary" type="submit">Save to wishlist</button></form>
        @endauth
        <div class="mt-4">{!! nl2br(e($product->description)) !!}</div>
    </div>
</div>
<section class="mt-5">
    <h2>Reviews</h2>
    @if ($ratingCount > 0)
        <p>Average {{ number_format($ratingAverage, 1) }} from {{ $ratingCount }} approved {{ \Illuminate\Support\Str::plural('review', $ratingCount) }}.</p>
    @else
        <p>No approved reviews yet.</p>
    @endif
    @foreach ($reviews as $review)
        <article class="border-bottom py-2">
            <strong>{{ $review->rating }} / 5</strong>
            @if ($review->title)<span> — {{ $review->title }}</span>@endif
            @if ($review->body)<p class="mb-0">{{ $review->body }}</p>@endif
        </article>
    @endforeach
</section>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'sku' => $product->sku,
    'description' => $product->short_description,
    'image' => url($product->primaryImagePath()),
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => $product->currency_code,
        'price' => $product->effectivePrice(),
        'availability' => $product->variants->contains(fn ($variant) => $variant->availableQuantity() > 0)
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
    ],
] + ($ratingCount > 0 ? ['aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => round($ratingAverage, 1), 'reviewCount' => $ratingCount]] : []), JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection
