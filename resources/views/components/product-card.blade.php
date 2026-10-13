<article class="product-card h-100">
    <a href="{{ route('products.show', $product) }}">
        <img src="{{ $product->primaryImagePath() }}" alt="{{ $product->images->first()->alt ?? $product->name }}">
    </a>
    <div class="p-3">
        <p class="text-secondary small mb-1 text-uppercase">{{ $product->gender }} · {{ str_replace('_', ' ', $product->fulfillment_type) }}</p>
        <h2 class="h5"><a class="text-decoration-none" href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h2>
        <p class="mb-2">@include('components.price', ['amount' => $product->effectivePrice(), 'from' => $product->currency_code])</p>
        @if ($product->isOnSale())
            <span class="status-badge">Sale</span>
        @endif
    </div>
</article>
