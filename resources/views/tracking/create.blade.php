@extends('layouts.store')
@section('title', 'Track an order')
@section('content')
<h1>Track an order</h1>
<p>Enter the order number and the email used at checkout. The tracking link uses a private token, not a sequential id by itself.</p>
<form method="POST" action="{{ route('tracking.store') }}" class="col-lg-6">
    @csrf
    @include('components.form-field', ['name' => 'number', 'label' => 'Order number'])
    @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email'])
    <x-button>Continue</x-button>
</form>
@endsection
