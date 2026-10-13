@extends('layouts.store')
@section('title', 'Register | Trend & Attire')
@section('content')
<h1>Create an account</h1>
<form method="POST" action="{{ route('register') }}" class="col-lg-6">
    @csrf
    @include('components.form-field', ['name' => 'name', 'label' => 'Name'])
    @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email'])
    @include('components.form-field', ['name' => 'phone', 'label' => 'Phone'])
    @include('components.form-field', ['name' => 'password', 'label' => 'Password', 'type' => 'password'])
    @include('components.form-field', ['name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password'])
    <x-button>Register</x-button>
</form>
@endsection
