@extends('layouts.store')
@section('title', 'Tracking '.$order->number)
@section('content')
<h1>{{ $order->number }}</h1>
<p>Payment @include('components.status-badge', ['status' => $order->payment_status]) Fulfillment @include('components.status-badge', ['status' => $order->fulfillment_status])</p>
@foreach ($order->items as $item)
    <p>{{ $item->name }} @if($item->production_status) · production {{ str_replace('_', ' ', $item->production_status) }} @endif</p>
@endforeach
@foreach ($order->shipments as $shipment)
    <p>{{ $shipment->carrier ?: 'Carrier not named' }} {{ $shipment->tracking_number ?: 'No tracking number yet' }}</p>
@endforeach
<h2 class="h5">Timeline</h2>
@foreach ($order->events as $event)
    <p>{{ $event->message }}</p>
@endforeach
@endsection
