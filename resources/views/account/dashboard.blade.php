@extends('layouts.store')
@section('title', 'Account | Trend & Attire')
@section('content')
<h1>Account</h1>
<ul class="nav mb-3">
    <li class="nav-item"><a class="nav-link" href="{{ route('account.profile') }}">Profile</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('account.addresses') }}">Addresses</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('account.orders') }}">Orders</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('wishlist.index') }}">Wishlist</a></li>
</ul>
<p>Saved addresses: {{ $addresses }}</p>
<h2 class="h4">Recent orders</h2>
@forelse ($orders as $order)
    <p><a href="{{ route('account.orders.show', $order) }}">{{ $order->number }}</a> @include('components.status-badge', ['status' => $order->payment_status])</p>
@empty
    <p>No orders yet.</p>
@endforelse
@endsection
