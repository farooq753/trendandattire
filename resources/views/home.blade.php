@extends('layouts.store')
@section('title', 'Trend & Attire | Scrub suits')
@section('content')
<section class="hero p-4 p-lg-5 mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <p class="text-uppercase text-secondary mb-2">Medical apparel</p>
            <h1 class="display-5">Scrub suits, ready to ship or stitched to measure.</h1>
            <p class="lead">Choose a size and color from stock, or configure a custom garment with your measurements. Prices are calculated on the server before checkout.</p>
            <a class="btn btn-navy me-2" href="{{ route('shop') }}">Shop scrubs</a>
            <a class="btn btn-teal" href="{{ route('custom.index') }}">Design custom scrubs</a>
        </div>
        <div class="col-lg-5">
            <img class="img-fluid placeholder-art" src="{{ asset('images/placeholders/scrub.svg') }}" alt="Illustration of a scrub suit">
        </div>
    </div>
</section>
@if ($banners->isNotEmpty())
    <div class="row g-3 mb-4">
        @foreach ($banners as $banner)
            <div class="col-md-6">
                <article class="p-4 bg-white border">
                    <h2 class="h4">{{ $banner->title }}</h2>
                    @if ($banner->subtitle)<p>{{ $banner->subtitle }}</p>@endif
                    @if ($banner->link_url)<a href="{{ url($banner->link_url) }}">View</a>@endif
                </article>
            </div>
        @endforeach
    </div>
@endif
<h2 class="h3 mb-3">Featured</h2>
<div class="row g-3">
    @forelse ($featured as $product)
        <div class="col-6 col-lg-3">@include('components.product-card', ['product' => $product])</div>
    @empty
        <p>New garments will appear here once they are published.</p>
    @endforelse
</div>
@endsection
