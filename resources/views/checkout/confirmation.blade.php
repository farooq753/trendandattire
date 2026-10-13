@extends('layouts.store')
@section('title', 'Order '.$order->number.' | Trend & Attire')
@section('content')
<h1>Order {{ $order->number }}</h1>
<p>Payment: @include('components.status-badge', ['status' => $order->payment_status]) Fulfillment: @include('components.status-badge', ['status' => $order->fulfillment_status])</p>
<p>Total @include('components.price', ['amount' => $order->grand_total, 'from' => $order->currency_code]) in {{ $order->currency_code }}. Saved rate {{ $order->fx_rate }} from {{ $order->fx_quoted_at }}.</p>
@foreach ($order->items as $item)
    <article class="bg-white border p-3 mb-2">
        <h2 class="h5">{{ $item->name }}</h2>
        <p>{{ $item->quantity }} × @include('components.price', ['amount' => $item->unit_price, 'from' => $order->currency_code])</p>
        @foreach ($item->configurations as $configuration)
            <p class="mb-0 small">{{ $configuration->label }}: {{ $configuration->value }}</p>
        @endforeach
        @if ($item->productionJob)
            <p>Production: @include('components.status-badge', ['status' => $item->productionJob->status])</p>
        @endif
    </article>
@endforeach
@if ($order->payment_method === 'bank_transfer' && $order->payment_status !== 'paid')
    <section class="bg-white border p-3 mt-3">
        <h2 class="h5">Bank transfer</h2>
        <p>Reference to use: <strong>{{ $order->number }}</strong>. The order stays unpaid until staff verify the transfer. Uploading a receipt does not mark it paid.</p>
        <ul>
            @if ($bank['account_name'])<li>Account name: {{ $bank['account_name'] }}</li>@endif
            @if ($bank['bank_name'])<li>Bank: {{ $bank['bank_name'] }}</li>@endif
            @if ($bank['account_number'])<li>Account: {{ $bank['account_number'] }}</li>@endif
            @if ($bank['swift'])<li>SWIFT: {{ $bank['swift'] }}</li>@endif
        </ul>
        @if ($bank['instructions'])<p>{{ $bank['instructions'] }}</p>@endif
        <form method="POST" action="{{ route('checkout.bank-evidence', $order) }}" enctype="multipart/form-data">
            @csrf
            <label class="form-label" for="bank_reference">Your transfer reference</label>
            <input class="form-control mb-2" id="bank_reference" name="bank_reference" required>
            <label class="form-label" for="evidence">Evidence (optional, private)</label>
            <input class="form-control mb-2" id="evidence" name="evidence" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf">
            <label class="form-label" for="customer_note">Note</label>
            <textarea class="form-control mb-2" id="customer_note" name="customer_note"></textarea>
            <x-button>Submit reference</x-button>
        </form>
    </section>
@endif
@if ($order->payment_method === 'cod')
    <p>Cash on delivery is unpaid until the store confirms the collection.</p>
@endif
<p><a href="{{ route('tracking.show', ['token' => $order->tracking_token]) }}">Tracking page</a></p>
@endsection
