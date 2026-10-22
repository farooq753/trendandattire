<div class="announce-bar">
    <p class="mb-0"><x-icon name="truck" /> Nationwide free delivery on orders above PKR 12,000</p>
</div>
<header class="site-header">
    <div class="container">
        <nav class="navbar navbar-expand-lg py-2" aria-label="Primary">
            <a class="brand" href="{{ route('home') }}">
                <svg class="brand-hanger" viewBox="0 0 64 40" aria-hidden="true">
                    <path d="M32 4a5 5 0 0 0-5 5c0 1.8.9 3.3 2.3 4.2L8 34h48L34.7 13.2A5 5 0 0 0 37 9a5 5 0 0 0-5-5z" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/>
                    <path d="M6 36h52" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <span>T&amp;A</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primary-nav" aria-controls="primary-nav" aria-expanded="false" aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="primary-nav">
                <ul class="navbar-nav mx-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('shop', 'products.show')) active @endif" href="{{ route('shop') }}">Shop</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('about')) active @endif" href="{{ route('about') }}">About us</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('size-guide')) active @endif" href="{{ route('size-guide') }}">Size guide</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Custom orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#reviews">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('contact')) active @endif" href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div class="header-tools">
                <button class="icon-btn" type="button" data-bs-toggle="collapse" data-bs-target="#header-search" aria-expanded="false" aria-controls="header-search" aria-label="Search">
                    <x-icon name="search" />
                </button>
                @auth
                    <a class="icon-btn" href="{{ route('account.dashboard') }}" aria-label="Account"><x-icon name="person" /></a>
                @else
                    <a class="icon-btn" href="{{ route('login') }}" aria-label="Log in"><x-icon name="person" /></a>
                @endauth
                <a class="icon-btn cart-btn" href="{{ route('cart.show') }}" aria-label="Cart, {{ $cartCount ?? 0 }} items">
                    <x-icon name="cart" />
                    <span class="cart-count">{{ $cartCount ?? 0 }}</span>
                </a>
            </div>
        </nav>
        <div class="collapse pb-3" id="header-search">
            <form class="header-search" method="GET" action="{{ route('search') }}" role="search">
                <label class="visually-hidden" for="q">Search scrub suits</label>
                <input class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Search name or SKU">
                <button class="btn btn-navy" type="submit">Search</button>
            </form>
        </div>
    </div>
</header>
