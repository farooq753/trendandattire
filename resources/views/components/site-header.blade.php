<header class="site-header">
    <div class="container py-3 d-flex flex-wrap align-items-center gap-3">
        <a class="brand-mark fs-4" href="{{ route('home') }}">Trend &amp; Attire</a>
        <form class="d-flex flex-grow-1" method="GET" action="{{ route('search') }}" role="search">
            <label class="visually-hidden" for="q">Search scrub suits</label>
            <input class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Search name or SKU">
            <button class="btn btn-navy ms-2" type="submit">Search</button>
        </form>
        <div class="d-flex gap-2 align-items-center">
            <a class="btn btn-outline-secondary" href="{{ route('cart.show') }}">Cart ({{ $cartCount ?? 0 }})</a>
            @auth
                <a class="btn btn-outline-secondary" href="{{ route('account.dashboard') }}">Account</a>
                @if (auth()->user()->isStaff())
                    <a class="btn btn-teal" href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-link" type="submit">Log out</button></form>
            @else
                <a class="btn btn-outline-secondary" href="{{ route('login') }}">Log in</a>
                <a class="btn btn-teal" href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>
    @include('components.site-nav')
</header>
