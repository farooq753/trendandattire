@extends('layouts.store')
@section('title', 'Addresses')
@section('content')
<h1>Addresses</h1>
@foreach ($addresses as $address)
    <article class="bg-white border p-3 mb-2">
        <h2 class="h6">{{ $address->label ?: $address->name }}</h2>
        <p class="mb-1">{{ $address->line1 }}, {{ $address->city }} {{ $address->postal_code }} {{ $address->country_code }}</p>
        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">@csrf @method('DELETE')<button class="btn btn-link p-0" type="submit">Remove</button></form>
    </article>
@endforeach
<h2 class="h4 mt-4">Add address</h2>
<form method="POST" action="{{ route('account.addresses.store') }}" class="col-lg-6">
    @csrf
    @include('components.form-field', ['name' => 'label', 'label' => 'Label'])
    @include('components.form-field', ['name' => 'name', 'label' => 'Name'])
    @include('components.form-field', ['name' => 'phone', 'label' => 'Phone'])
    <div class="mb-3"><label class="form-label" for="addr_country">Country</label><select class="form-select" id="addr_country" name="country_code" required>@foreach ($storeCountries as $country)<option value="{{ $country->code }}">{{ $country->name }}</option>@endforeach</select></div>
    @include('components.form-field', ['name' => 'region', 'label' => 'Region'])
    @include('components.form-field', ['name' => 'city', 'label' => 'City'])
    @include('components.form-field', ['name' => 'line1', 'label' => 'Address line 1'])
    @include('components.form-field', ['name' => 'line2', 'label' => 'Address line 2'])
    @include('components.form-field', ['name' => 'postal_code', 'label' => 'Postal code'])
    <div class="form-check"><input class="form-check-input" type="checkbox" name="is_default_shipping" id="def-ship" value="1"><label class="form-check-label" for="def-ship">Default shipping</label></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_default_billing" id="def-bill" value="1"><label class="form-check-label" for="def-bill">Default billing</label></div>
    <x-button>Save address</x-button>
</form>
@endsection
