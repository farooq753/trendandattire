@extends('layouts.store')
@section('title', 'Custom scrubs | Trend & Attire')
@section('content')
<h1>Custom scrubs</h1>
<p>Custom garments are made to your configuration. They are not taken from ready-made inventory.</p>
<div class="row g-3">
    @forelse ($products as $product)
        <div class="col-6 col-lg-3">@include('components.product-card', ['product' => $product])</div>
    @empty
        <p>No custom garments are published.</p>
    @endforelse
</div>
@endsection
