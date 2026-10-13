@extends('layouts.admin')
@section('content')
<h1>Coupons</h1>
<a class="btn btn-navy" href="{{ route('admin.coupons.create') }}">New coupon</a>
@foreach ($coupons as $coupon)<p><a href="{{ route('admin.coupons.edit', $coupon) }}">{{ $coupon->code }}</a> {{ $coupon->type }} {{ $coupon->value }}</p>@endforeach
@endsection
