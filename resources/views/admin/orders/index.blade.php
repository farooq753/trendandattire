@extends('layouts.admin')
@section('content')
<h1>Orders</h1>
@foreach ($orders as $order)
<p><a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a> {{ $order->customer_email }} @include('components.status-badge', ['status' => $order->payment_status])</p>
@endforeach
{{ $orders->links() }}
@endsection
