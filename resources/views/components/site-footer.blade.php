<footer class="site-footer mt-5 py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="h5">Trend &amp; Attire</h2>
                <p>Ready-made scrub suits. {{ __('store.duties') }}</p>
                @php($location = config('store.location'))
                <address class="small mb-2">
                    @foreach ($location['lines'] as $line)
                        {{ $line }}@if (! $loop->last)<br>@endif
                    @endforeach
                </address>
                <p class="small mb-0"><a href="{{ $location['maps_url'] }}" rel="noopener noreferrer" target="_blank">Google Maps</a></p>
            </div>
            <div class="col-md-4">
                <h2 class="h6">Help</h2>
                <ul class="list-unstyled">
                    <li><a href="{{ route('size-guide') }}">Size guide</a></li>
                    <li><a href="{{ route('shipping') }}">Shipping</a></li>
                    <li><a href="{{ route('returns.policy') }}">Returns</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                    <li><a href="{{ route('tracking.create') }}">Track an order</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h2 class="h6">Company</h2>
                <ul class="list-unstyled">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                    <li><a href="{{ route('privacy') }}">Privacy</a></li>
                    <li><a href="{{ route('terms') }}">Terms</a></li>
                </ul>
                <form method="POST" action="{{ route('preferences') }}" class="row g-2">
                    @csrf
                    <div class="col-6">
                        <label class="form-label" for="country_code">Country</label>
                        <select class="form-select" id="country_code" name="country_code" onchange="this.form.submit()">
                            <option value="">Select</option>
                            @foreach (($storeCountries ?? []) as $country)
                                <option value="{{ $country->code }}" @selected(session('country_code') === $country->code)>{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="currency_code">Currency</label>
                        <select class="form-select" id="currency_code" name="currency_code" onchange="this.form.submit()">
                            @foreach (($storeCurrencies ?? []) as $currency)
                                <option value="{{ $currency->code }}" @selected(($activeCurrency ?? 'USD') === $currency->code)>{{ $currency->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <p class="small text-secondary mt-2 mb-0">{{ __('store.fx') }}</p>
            </div>
        </div>
    </div>
</footer>
