@extends('layouts.store')
@section('title', 'Verify email')
@section('content')
<h1>Verify your email</h1>
<p>Email verification is required by the current store setting.</p>
<form method="POST" action="{{ route('verification.send') }}">@csrf<x-button>Resend link</x-button></form>
@endsection
