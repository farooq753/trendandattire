@extends('layouts.store')
@section('title', 'Orders')
@section('content')
<h1>Orders</h1>
@forelse ($orders as $order)
    <p><a href="{{ route('account.orders.show', $order) }}">{{ $order->number }}</a> {{ $order->placed_at->timezone(auth()->user()->timezone)->toDayDateTimeString() }} @include('components.status-badge', ['status' => $order->payment_status]) @include('components.status-badge', ['status' => $order->fulfillment_status])</p>
@empty
    <p>You have no orders.</p>
@endforelse
{{ $orders->links() }}
@endsection
