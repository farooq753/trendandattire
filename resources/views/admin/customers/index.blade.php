@extends('layouts.admin')
@section('content')
<h1>Customers</h1>
<form method="GET"><label class="visually-hidden" for="q">Search</label><input id="q" name="q" value="{{ request('q') }}"><button class="btn btn-navy" type="submit">Search</button></form>
@foreach ($customers as $customer)<p><a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a> {{ $customer->email }}</p>@endforeach
{{ $customers->links() }}
@endsection
