@extends('layouts.store')
@section('title', 'Contact | Trend & Attire')
@section('content')
<h1>Contact</h1>
<form method="POST" action="{{ route('contact.send') }}" class="col-lg-7">
    @csrf
    @include('components.form-field', ['name' => 'name', 'label' => 'Name'])
    @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email'])
    @include('components.form-field', ['name' => 'subject', 'label' => 'Subject'])
    <div class="mb-3"><label class="form-label" for="message">Message</label><textarea class="form-control" id="message" name="message" required>{{ old('message') }}</textarea></div>
    <x-button>Send</x-button>
</form>
@endsection
