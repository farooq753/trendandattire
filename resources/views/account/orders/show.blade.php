@extends('layouts.store')
@section('title', $order->number)
@section('content')
<h1>{{ $order->number }}</h1>
<p>Payment @include('components.status-badge', ['status' => $order->payment_status]) Fulfillment @include('components.status-badge', ['status' => $order->fulfillment_status])</p>
@foreach ($order->items as $item)
    <article class="bg-white border p-3 mb-2">
        <h2 class="h5">{{ $item->name }}</h2>
        <p>{{ $item->sku }} · {{ $item->quantity }} · @include('components.price', ['amount' => $item->line_total, 'from' => $order->currency_code])</p>
        @if ($item->production_status)<p>Production @include('components.status-badge', ['status' => $item->production_status])</p>@endif
        @if ($showMeasurements)
            @foreach ($item->measurements as $measurement)
                <p class="small mb-0">{{ $measurement->label }}: {{ $measurement->original_value }} {{ $measurement->original_unit }}</p>
            @endforeach
        @endif
    </article>
@endforeach
<h2 class="h5">Shipments</h2>
@forelse ($order->shipments as $shipment)
    <p>{{ $shipment->carrier ?: 'Manual carrier' }} {{ $shipment->tracking_number }} @include('components.status-badge', ['status' => $shipment->status])</p>
@empty
    <p>No shipment has been recorded.</p>
@endforelse
<h2 class="h5">Timeline</h2>
@foreach ($order->events as $event)
    <p class="mb-1">{{ $event->created_at->timezone(auth()->user()->timezone) }} — {{ $event->message }}</p>
@endforeach
@if (! $order->cancelled_at && in_array($order->payment_status, ['pending', 'awaiting_payment', 'evidence_submitted']))
    <form method="POST" action="{{ route('account.orders.cancel', $order) }}">@csrf<x-button variant="outline-danger">Cancel order</x-button></form>
@endif
@if (in_array($order->fulfillment_status, ['partial', 'fulfilled']))
    <h2 class="h5 mt-4">Request a return</h2>
    <form method="POST" action="{{ route('account.returns.store', $order) }}">
        @csrf
        <label class="form-label" for="reason">Reason</label>
        <input class="form-control mb-2" id="reason" name="reason" required>
        @foreach ($order->items as $index => $item)
            <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
            <label class="form-label" for="qty-return-{{ $item->id }}">{{ $item->name }} quantity</label>
            <input class="form-control mb-2" id="qty-return-{{ $item->id }}" name="items[{{ $index }}][quantity]" type="number" min="1" max="{{ $item->quantity }}" value="1">
        @endforeach
        <x-button>Request return</x-button>
    </form>
@endif
@endsection
