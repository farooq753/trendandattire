@extends('layouts.store')
@section('title', 'Password recovery | Trend & Attire')
@section('content')
<h1>Password recovery</h1>
<form method="POST" action="{{ route('password.email') }}" class="col-lg-6">
    @csrf
    @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email'])
    <x-button>Email reset link</x-button>
</form>
@endsection
