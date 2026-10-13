@extends('layouts.admin')
@section('content')
<h1>{{ $customer->name }}</h1>
<p>{{ $customer->email }} @if($customer->deletion_requested_at) Deletion requested {{ $customer->deletion_requested_at }} @endif</p>
@foreach ($customer->orders as $order)<p><a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a></p>@endforeach
@endsection
