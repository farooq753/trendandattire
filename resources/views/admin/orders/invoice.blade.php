@extends('layouts.admin')
@section('content')
<h1>Invoice {{ $order->number }}</h1>
<p>{{ $order->customer_name }} · {{ $order->customer_email }}</p>
@foreach ($order->items as $item)<p>{{ $item->name }} × {{ $item->quantity }} = {{ $item->line_total }} {{ $order->currency_code }}</p>@endforeach
<p>Total {{ $order->grand_total }} {{ $order->currency_code }}</p>
<p>Payment {{ $order->payment_status }}. Cost prices are omitted from customer documents.</p>
@endsection
