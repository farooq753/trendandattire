@extends('layouts.store')
@section('title', 'Log in | Trend & Attire')
@section('content')
<h1>Log in</h1>
<form method="POST" action="{{ route('login') }}" class="col-lg-6">
    @csrf
    @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email'])
    @include('components.form-field', ['name' => 'password', 'label' => 'Password', 'type' => 'password'])
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember" value="1"><label class="form-check-label" for="remember">Remember me</label></div>
    <x-button>Log in</x-button>
    <a class="ms-3" href="{{ route('password.request') }}">Forgot password</a>
</form>
@endsection
