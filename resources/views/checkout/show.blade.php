@extends('layouts.store')
@section('title', 'Checkout | Trend & Attire')
@section('content')
<h1>Checkout</h1>
<p>Prices, tax, shipping, and stock are recalculated on the server. The currency for this order is {{ $quote['currency'] }} using saved rate {{ $quote['fx_rate'] }} quoted {{ optional($quote['fx_quoted_at'])->toDateTimeString() }}. It will not change after the order is placed.</p>
<form method="POST" action="{{ route('checkout.store') }}">
    @csrf
    <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
    <div class="row g-4">
        <div class="col-lg-7">
            <h2 class="h4">Contact</h2>
            @include('components.form-field', ['name' => 'name', 'label' => 'Full name', 'value' => auth()->user()->name ?? ''])
            @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => auth()->user()->email ?? ''])
            @include('components.form-field', ['name' => 'phone', 'label' => 'Phone', 'value' => auth()->user()->phone ?? ''])
            <h2 class="h4">Shipping address</h2>
            @include('components.form-field', ['name' => 'shipping[name]', 'label' => 'Recipient', 'value' => auth()->user()->name ?? ''])
            @include('components.form-field', ['name' => 'shipping[phone]', 'label' => 'Phone', 'value' => auth()->user()->phone ?? ''])
            <div class="mb-3">
                <label class="form-label" for="shipping_country">Country</label>
                <select class="form-select" id="shipping_country" name="shipping[country_code]" required>
                    @foreach ($storeCountries as $country)
                        <option value="{{ $country->code }}" @selected(old('shipping.country_code', session('country_code')) === $country->code)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            @include('components.form-field', ['name' => 'shipping[region]', 'label' => 'Region'])
            @include('components.form-field', ['name' => 'shipping[city]', 'label' => 'City'])
            @include('components.form-field', ['name' => 'shipping[line1]', 'label' => 'Address line 1'])
            @include('components.form-field', ['name' => 'shipping[line2]', 'label' => 'Address line 2'])
            @include('components.form-field', ['name' => 'shipping[postal_code]', 'label' => 'Postal code'])
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="billing_same" name="billing_same" value="1" checked>
                <label class="form-check-label" for="billing_same">Billing address is the same</label>
            </div>
            <h2 class="h4">Shipping method</h2>
            @forelse ($quote['shipping_options'] as $option)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="shipping_method_id" id="ship-{{ $option['method']->id }}" value="{{ $option['method']->id }}" @checked(old('shipping_method_id') == $option['method']->id)>
                    <label class="form-check-label" for="ship-{{ $option['method']->id }}">
                        {{ $option['method']->name }} — @include('components.price', ['amount' => $option['amount'], 'from' => $quote['fx_base']])
                        <span class="d-block small text-secondary">Processing {{ $option['method']->processing_min_days }}–{{ $option['method']->processing_max_days }} days. Estimate only.</span>
                    </label>
                </div>
            @empty
                <p>Choose a country with a configured shipping zone. No live DHL, FedEx, or UPS rates are requested.</p>
            @endforelse
            <h2 class="h4 mt-4">Payment</h2>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="pay-cod" value="cod" @checked(old('payment_method') === 'cod')>
                <label class="form-check-label" for="pay-cod">Cash on delivery where the method and amount allow it. The order stays unpaid until staff confirm collection.</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="pay-bank" value="bank_transfer" @checked(old('payment_method', 'bank_transfer') === 'bank_transfer')>
                <label class="form-check-label" for="pay-bank">Bank transfer. You will receive a reference. Sending a receipt does not mark the order paid.</label>
            </div>
            <p class="small text-secondary">Card and PayPal stay unavailable until provider credentials are configured. This checkout never stores a card number.</p>
            <label class="form-label" for="notes">Notes</label>
            <textarea class="form-control mb-3" id="notes" name="notes">{{ old('notes') }}</textarea>
            <x-button>Place order</x-button>
        </div>
        <div class="col-lg-5">
            <aside class="bg-white border p-3">
                <h2 class="h5">Summary</h2>
                @foreach ($quote['lines'] as $line)
                    <p>{{ $line['quantity'] }} × {{ $line['name'] }} — @include('components.price', ['amount' => $line['line_total'], 'from' => $quote['currency']])</p>
                @endforeach
                <p>Subtotal @include('components.price', ['amount' => $quote['subtotal'], 'from' => $quote['currency']])</p>
                <p>Discount @include('components.price', ['amount' => $quote['discount'], 'from' => $quote['currency']])</p>
                <p>Shipping @include('components.price', ['amount' => $quote['shipping'], 'from' => $quote['currency']])</p>
                <p>Tax @include('components.price', ['amount' => $quote['tax'], 'from' => $quote['currency']])</p>
                @if ($quote['tax_lines'] === [])
                    <p class="small">No store tax rule is configured for the selected destination. This is not a statement that no tax is legally due.</p>
                @endif
                <p>COD fee @include('components.price', ['amount' => $quote['cod_fee'], 'from' => $quote['currency']])</p>
                <p class="fw-semibold">Total @include('components.price', ['amount' => $quote['total'], 'from' => $quote['currency']])</p>
                <p class="small">Import duties are not collected here. The recipient may have to pay them on delivery. Duty-free delivery is not promised.</p>
            </aside>
        </div>
    </div>
</form>
@endsection
