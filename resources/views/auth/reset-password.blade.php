@extends('layouts.store')
@section('title', 'Choose a new password')
@section('content')
<h1>Choose a new password</h1>
<form method="POST" action="{{ route('password.update') }}" class="col-lg-6">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $email])
    @include('components.form-field', ['name' => 'password', 'label' => 'Password', 'type' => 'password'])
    @include('components.form-field', ['name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password'])
    <x-button>Update password</x-button>
</form>
@endsection
