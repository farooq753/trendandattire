@extends('layouts.admin')
@section('content')
<h1>Packing slip {{ $order->number }}</h1>
@php $address = $order->addresses->firstWhere('type', 'shipping'); @endphp
@if ($address)<p>{{ $address->name }}, {{ $address->line1 }}, {{ $address->city }}, {{ $address->country_code }}</p>@endif
@foreach ($order->items as $item)<p>{{ $item->sku }} · {{ $item->name }} × {{ $item->quantity }}</p>@endforeach
@endsection
