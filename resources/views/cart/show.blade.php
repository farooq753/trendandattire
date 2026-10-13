@extends('layouts.store')
@section('title', 'Cart | Trend & Attire')
@section('content')
<h1>Cart</h1>
@forelse ($quote['lines'] as $line)
    <article class="bg-white border p-3 mb-3">
        <div class="d-flex justify-content-between gap-3">
            <div>
                <h2 class="h5">{{ $line['name'] }}</h2>
                <p class="mb-1">{{ str_replace('_', ' ', $line['fulfillment']) }} @if($line['variant']) · {{ $line['variant']->label() }} @endif</p>
                @foreach ($line['configuration'] as $option)
                    @if (is_array($option) && isset($option['label']))
                        <p class="mb-0 small">{{ $option['label'] }}: {{ $option['value'] }}</p>
                    @endif
                @endforeach
                @if ($line['error'])<p class="text-danger mb-0">{{ $line['error'] }}</p>@endif
            </div>
            <div class="text-end">
                <p>@include('components.price', ['amount' => $line['line_total'], 'from' => $quote['currency']])</p>
                <form method="POST" action="{{ route('cart.update', $line['item']) }}">
                    @csrf @method('PATCH')
                    <label class="visually-hidden" for="qty-{{ $line['item']->id }}">Quantity</label>
                    <input id="qty-{{ $line['item']->id }}" name="quantity" type="number" min="1" max="20" value="{{ $line['quantity'] }}">
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Update</button>
                </form>
                <form method="POST" action="{{ route('cart.destroy', $line['item']) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-link" type="submit">Remove</button></form>
            </div>
        </div>
    </article>
@empty
    <p>Your cart is empty. <a href="{{ route('shop') }}">Continue shopping</a>.</p>
@endforelse
@if ($quote['lines'] !== [])
    <form method="POST" action="{{ route('cart.coupon') }}" class="row g-2 mb-3">
        @csrf
        <div class="col-auto"><label class="visually-hidden" for="code">Coupon</label><input class="form-control" id="code" name="code" value="{{ $cart->coupon_code }}" placeholder="Coupon"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary" type="submit">Apply</button></div>
    </form>
    @if ($quote['discount_error'])<p class="text-danger">{{ $quote['discount_error'] }}</p>@endif
    <dl class="row">
        <dt class="col-6">Subtotal</dt><dd class="col-6 text-end">@include('components.price', ['amount' => $quote['subtotal'], 'from' => $quote['currency']])</dd>
        <dt class="col-6">Discount</dt><dd class="col-6 text-end">@include('components.price', ['amount' => $quote['discount'], 'from' => $quote['currency']])</dd>
        <dt class="col-6">Estimated shipping</dt><dd class="col-6 text-end">Select a country to estimate. Estimates are not guarantees.</dd>
        <dt class="col-6">Total so far</dt><dd class="col-6 text-end">@include('components.price', ['amount' => $quote['total'], 'from' => $quote['currency']])</dd>
    </dl>
    <a class="btn btn-navy" href="{{ route('checkout.show') }}">Checkout</a>
@endif
@endsection
