@extends('layouts.store')
@section('title', 'Contact | Trend & Attire')
@section('content')
@php($location = config('store.location'))
<h1>Contact</h1>
<div class="row g-4">
    <form method="POST" action="{{ route('contact.send') }}" class="col-lg-7">
        @csrf
        @include('components.form-field', ['name' => 'name', 'label' => 'Name'])
        @include('components.form-field', ['name' => 'email', 'label' => 'Email', 'type' => 'email'])
        @include('components.form-field', ['name' => 'subject', 'label' => 'Subject'])
        <div class="mb-3"><label class="form-label" for="message">Message</label><textarea class="form-control" id="message" name="message" required>{{ old('message') }}</textarea></div>
        <x-button>Send</x-button>
    </form>
    <aside class="col-lg-5">
        <h2 class="h5">Shop</h2>
        <address class="mb-2">
            <strong>{{ $location['name'] }}</strong><br>
            @foreach ($location['lines'] as $line)
                {{ $line }}@if (! $loop->last)<br>@endif
            @endforeach
        </address>
        <p class="mb-1"><a href="tel:{{ $location['phone'] }}">{{ $location['phone_display'] }}</a></p>
        <p class="mb-2">{{ $location['hours'] }}</p>
        <p class="mb-3"><a href="{{ $location['maps_url'] }}" rel="noopener noreferrer" target="_blank">Open in Google Maps</a></p>
        <iframe class="store-map" title="Trend and Attire on Google Maps" src="{{ $location['embed_url'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </aside>
</div>
@endsection
