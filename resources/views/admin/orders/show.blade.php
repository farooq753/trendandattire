@extends('layouts.admin')
@section('content')
<h1>{{ $order->number }}</h1>
<p>@include('components.status-badge', ['status' => $order->payment_status]) @include('components.status-badge', ['status' => $order->fulfillment_status])</p>
<p><a href="{{ route('admin.orders.invoice', $order) }}">Invoice</a> · <a href="{{ route('admin.orders.packing-slip', $order) }}">Packing slip</a></p>
@foreach ($order->items as $item)
<p>{{ $item->name }} × {{ $item->quantity }} (shipped {{ $item->shipmentItems->sum('quantity') }})</p>
@endforeach
@if (auth()->user()->hasPermission('payments.verify') && $order->payment)
    @if ($order->payment->method === 'bank_transfer')
        <form method="POST" action="{{ route('admin.payments.verify-bank', $order->payment) }}">@csrf<button class="btn btn-teal" type="submit">Verify bank transfer</button></form>
        <form method="POST" action="{{ route('admin.payments.reject-bank', $order->payment) }}">@csrf<button class="btn btn-outline-secondary" type="submit">Reject evidence</button></form>
    @endif
    @if ($order->payment->method === 'cod')
        <form method="POST" action="{{ route('admin.payments.collect-cod', $order->payment) }}">@csrf<button class="btn btn-teal" type="submit">Confirm COD collection</button></form>
    @endif
@endif
@if (auth()->user()->hasPermission('refunds.manage') && $order->payment)
<form method="POST" action="{{ route('admin.payments.refund', $order->payment) }}" class="mt-3">@csrf
<label class="form-label" for="amount">Refund amount</label><input class="form-control mb-2" id="amount" name="amount">
<label class="form-label" for="reason">Reason</label><input class="form-control mb-2" id="reason" name="reason">
<button class="btn btn-outline-secondary" type="submit">Record refund</button></form>
@endif
@if (auth()->user()->hasPermission('orders.manage'))
<h2 class="h5 mt-4">Manual shipment</h2>
<form method="POST" action="{{ route('admin.orders.ship', $order) }}">
@csrf
<label class="form-label" for="carrier">Carrier label</label><input class="form-control mb-2" id="carrier" name="carrier" placeholder="Manual / DHL / FedEx / UPS">
<label class="form-label" for="tracking_number">Tracking number</label><input class="form-control mb-2" id="tracking_number" name="tracking_number">
@foreach ($order->items as $index => $item)
<input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
<label class="form-label" for="ship-qty-{{ $item->id }}">{{ $item->name }} quantity</label>
<input class="form-control mb-2" id="ship-qty-{{ $item->id }}" name="items[{{ $index }}][quantity]" type="number" min="1" value="{{ max($item->quantity - $item->shipmentItems->sum('quantity'), 0) }}">
@endforeach
<button class="btn btn-navy" type="submit">Record shipment</button>
</form>
@endif
@endsection
