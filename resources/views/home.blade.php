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
            <li><i class="bi bi-shield-check" aria-hidden="true"></i><strong>100% quality checked</strong><span>Every product passes strict quality checks</span></li>
            <li><i class="bi bi-heart-pulse" aria-hidden="true"></i><strong>Designed by doctors</strong><span>Engineered for comfort and performance</span></li>
            <li><i class="bi bi-scissors" aria-hidden="true"></i><strong>Custom embroidery</strong><span>Personalize your scrubs</span></li>
            <li><i class="bi bi-bezier2" aria-hidden="true"></i><strong>Premium stitching</strong><span>Precise stitching for durability</span></li>
            <li><i class="bi bi-box-seam" aria-hidden="true"></i><strong>Fast dispatch</strong><span>Quick processing and dispatch</span></li>
            <li><i class="bi bi-truck" aria-hidden="true"></i><strong>Nationwide delivery</strong><span>Reliable delivery across Pakistan</span></li>
        </ul>
    </div>
</section>

<section class="home-collections" id="collections">
    <div class="container">
        <h2 class="section-kicker">Shop by collection</h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <article class="collection-card collection-signature">
                    <div>
                        <h3>Signature collection</h3>
                        <p>90% cotton | 10% polyester<br>Custom tailoring available</p>
                        <a class="btn btn-light" href="{{ route('shop') }}">Shop signature</a>
                    </div>
                    <img src="{{ asset('images/home/signature-clinician.jpg') }}" alt="Clinician wearing the signature navy scrub suit">
                </article>
            </div>
            <div class="col-lg-6">
                <article class="collection-card collection-essential">
                    <div>
                        <h3>Essential collection</h3>
                        <p>70% cotton | 30% polyester<br>Ready-made fit</p>
                        <a class="btn btn-light" href="{{ route('shop') }}">Shop essential</a>
                    </div>
                    <img src="{{ asset('images/home/essential-clinician.jpg') }}" alt="Clinician wearing the essential navy scrub suit">
                </article>
            </div>
        </div>
    </div>
</section>


<section class="home-reviews" id="reviews">
    <div class="container">
        <h2 class="section-kicker">What our customers say</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="review-card">
                    <img src="{{ asset('images/home/signature-clinician.jpg') }}" alt="">
                    <p class="stars" aria-label="5 stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></p>
                    <h3>Dr. Ahmed Khan</h3>
                    <p class="review-role">Orthopaedic Surgeon<br>Karachi</p>
                    <blockquote>“The fabric quality is extraordinary. Even after repeated washing, the colour remains the same.”</blockquote>
                </article>
            </div>
            <div class="col-md-4">
                <article class="review-card">
                    <img src="{{ asset('images/home/essential-clinician.jpg') }}" alt="">
                    <p class="stars" aria-label="5 stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></p>
                    <h3>Dr. Hina Malik</h3>
                    <p class="review-role">General Physician<br>Lahore</p>
                    <blockquote>“Finally found scrubs that are comfortable for long shifts and look professional too.”</blockquote>
                </article>
            </div>
            <div class="col-md-4">
                <article class="review-card">
                    <img src="{{ asset('images/home/signature-clinician.jpg') }}" alt="">
                    <p class="stars" aria-label="5 stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></p>
                    <h3>Dr. Usman Tariq</h3>
                    <p class="review-role">Cardiologist<br>Islamabad</p>
                    <blockquote>“Excellent stitching, perfect fit and very professional customer service. Highly recommended.”</blockquote>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
