@extends('layouts.store')
@section('title', 'Trend & Attire | Premium medical apparel')
@section('description', 'Premium medical apparel designed by doctors. Navy scrub suits with quality checks, precise stitching, and nationwide delivery across Pakistan.')
@section('content')
<section class="home-hero">
    <div class="home-hero-copy">
        <p class="hero-mark">T&amp;A</p>
        <p class="hero-brand">Trend &amp; Attire</p>
        <h1>Premium Medical Apparel<br>Designed by Doctors</h1>
        <p class="hero-points">Engineered for comfort.<br>Crafted for confidence.<br>Trusted for quality.</p>
        <div class="hero-actions">
            <a class="btn btn-light hero-btn" href="{{ route('shop') }}">Shop scrubs</a>
            <a class="btn btn-outline-light hero-btn" href="#collections">Explore collections</a>
        </div>
    </div>
    <div class="home-hero-photo">
        <img src="{{ asset('images/home/hero-clinicians.jpg') }}" alt="Two clinicians wearing navy Trend and Attire scrubs in a hospital corridor">
    </div>
</section>

<section class="home-trust" aria-label="Why shop with Trend and Attire">
    <div class="container">
        <ul class="trust-grid">
            <li><x-icon name="shield" /><strong>100% quality checked</strong><span>Every product passes strict quality checks</span></li>
            <li><x-icon name="pulse" /><strong>Designed by doctors</strong><span>Engineered for comfort and performance</span></li>
            <li><x-icon name="scissors" /><strong>Custom embroidery</strong><span>Personalize your scrubs</span></li>
            <li><x-icon name="needle" /><strong>Premium stitching</strong><span>Precise stitching for durability</span></li>
            <li><x-icon name="box" /><strong>Fast dispatch</strong><span>Quick processing and dispatch</span></li>
            <li><x-icon name="truck" /><strong>Nationwide delivery</strong><span>Reliable delivery across Pakistan</span></li>
        </ul>
    </div>
</section>

<section class="home-collections" id="collections" data-color-root="{{ asset('images/products/colors') }}">
    <div class="container">
        <h2 class="section-kicker">Shop by collection</h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <article class="collection-card collection-signature">
                    <div>
                        <h3>Signature Scrub Collection</h3>
                        <p class="collection-price">PKR 6,500</p>
                        <ul>
                            <li>Ready to wear</li>
                            <li>Breathable premium-quality fabric, 90% cotton</li>
                            <li>Wrinkle-free, colour fast, no shrinkage</li>
                        </ul>
                        <div class="collection-swatches" aria-label="Signature colours">
                            @foreach ($scrubColours as $colour)
                                <button class="color-swatch @if($colour->slug === 'black') is-selected @endif" type="button" data-color="{{ $colour->slug }}" style="background: {{ $colour->meta['hex'] ?? '#cccccc' }}" aria-label="{{ $colour->value }}" aria-pressed="{{ $colour->slug === 'black' ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <a class="btn btn-light collection-shop" href="{{ route('products.show', 'signature-scrub-collection') }}">Shop signature</a>
                    </div>
                    <img class="collection-photo" data-file="scrub-set.jpg" data-original="{{ asset('images/products/scrub-set.jpg') }}" src="{{ asset('images/products/scrub-set.jpg') }}" alt="Signature scrub top, pants, and cap">
                </article>
            </div>
            <div class="col-lg-6">
                <article class="collection-card collection-essential">
                    <div>
                        <h3>Essential Scrub Collection</h3>
                        <p class="collection-price">PKR 4,500</p>
                        <ul>
                            <li>Ready to wear</li>
                            <li>Breathable premium-quality fabric, 70% cotton</li>
                            <li>Wrinkle-free, colour fast, no shrinkage</li>
                        </ul>
                        <div class="collection-swatches" aria-label="Essential colours">
                            @foreach ($scrubColours as $colour)
                                <button class="color-swatch @if($colour->slug === 'black') is-selected @endif" type="button" data-color="{{ $colour->slug }}" style="background: {{ $colour->meta['hex'] ?? '#cccccc' }}" aria-label="{{ $colour->value }}" aria-pressed="{{ $colour->slug === 'black' ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <a class="btn btn-light collection-shop" href="{{ route('products.show', 'essential-scrub-collection') }}">Shop essential</a>
                    </div>
                    <img class="collection-photo" data-file="scrub-side.jpg" data-original="{{ asset('images/products/scrub-side.jpg') }}" src="{{ asset('images/products/scrub-side.jpg') }}" alt="Side view of the essential scrub suit">
                </article>
            </div>
        </div>
    </div>
</section>

<section class="home-addons" id="add-ons">
    <div class="container">
        <h2 class="section-kicker">Add-ons</h2>
        <div class="row g-4 justify-content-center">
            @foreach ($addons as $addon)
                <div class="col-md-6 col-lg-4">
                    <article class="product-card addon-card h-100">
                        <a href="{{ route('products.show', $addon) }}">
                            <img src="{{ $addon->primaryImagePath() }}" alt="{{ $addon->images->first()->alt ?? $addon->name }}">
                        </a>
                        <div class="p-3">
                            <p class="addon-kicker">Discounted price</p>
                            <h3 class="h5"><a class="text-decoration-none" href="{{ route('products.show', $addon) }}">{{ $addon->name }}</a></h3>
                            <p class="mb-1">@include('components.price', ['amount' => $addon->effectivePrice(), 'from' => $addon->currency_code])</p>
                            <p class="text-secondary mb-0">{{ $addon->slug === 'scrub-cap' ? 'Standard size' : 'Sizes: S, M, L, XL' }}</p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="home-reasons">
    <div class="container">
        <h2 class="section-kicker">Why healthcare professionals choose T&amp;A</h2>
        <ul class="reason-grid">
            <li><x-icon name="layers" /><strong>Premium fabrics</strong></li>
            <li><x-icon name="wind" /><strong>Breathable comfort</strong></li>
            <li><x-icon name="crease" /><strong>Wrinkle resistant</strong></li>
            <li><x-icon name="droplet" /><strong>No colour bleed</strong></li>
            <li><x-icon name="box" /><strong>Ready to wear</strong></li>
            <li><x-icon name="truck" /><strong>Nationwide free delivery</strong></li>
        </ul>
    </div>
</section>

<section class="home-reviews" id="reviews">
    <div class="container">
        <h2 class="section-kicker">What our customers say</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="review-card">
                    <img src="{{ asset('images/home/signature-clinician.jpg') }}" alt="">
                    <p class="stars" aria-label="5 stars">@for ($star = 0; $star < 5; $star++)<x-icon name="star" />@endfor</p>
                    <h3>Dr. Ahmed Khan</h3>
                    <p class="review-role">Orthopaedic Surgeon<br>Karachi</p>
                    <blockquote>“The fabric quality is extraordinary. Even after repeated washing, the colour remains the same.”</blockquote>
                </article>
            </div>
            <div class="col-md-4">
                <article class="review-card">
                    <img src="{{ asset('images/home/essential-clinician.jpg') }}" alt="">
                    <p class="stars" aria-label="5 stars">@for ($star = 0; $star < 5; $star++)<x-icon name="star" />@endfor</p>
                    <h3>Dr. Hina Malik</h3>
                    <p class="review-role">General Physician<br>Lahore</p>
                    <blockquote>“Finally found scrubs that are comfortable for long shifts and look professional too.”</blockquote>
                </article>
            </div>
            <div class="col-md-4">
                <article class="review-card">
                    <img src="{{ asset('images/home/signature-clinician.jpg') }}" alt="">
                    <p class="stars" aria-label="5 stars">@for ($star = 0; $star < 5; $star++)<x-icon name="star" />@endfor</p>
                    <h3>Dr. Usman Tariq</h3>
                    <p class="review-role">Cardiologist<br>Islamabad</p>
                    <blockquote>“Excellent stitching, perfect fit and very professional customer service. Highly recommended.”</blockquote>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
