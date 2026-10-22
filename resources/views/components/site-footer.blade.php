@php($location = config('store.location'))
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <a class="footer-brand" href="{{ route('home') }}">T&amp;A</a>
                <p>Premium medical apparel designed by doctors. Crafted for comfort. Trusted for quality.</p>
                <div class="social-icons">
                    <a href="https://facebook.com/106564844552933" rel="noopener noreferrer" target="_blank" aria-label="Facebook"><x-icon name="facebook" /></a>
                    <span class="social-mark" aria-label="Instagram"><x-icon name="instagram" /></span>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h2>Shop</h2>
                <ul>
                    <li><a href="{{ route('shop') }}">All products</a></li>
                    <li><a href="{{ route('products.show', 'signature-scrub-collection') }}">Signature scrubs</a></li>
                    <li><a href="{{ route('products.show', 'essential-scrub-collection') }}">Essential scrubs</a></li>
                </ul>
                <h2 class="footer-subhead">Add-ons</h2>
                <ul>
                    <li><a href="{{ route('products.show', 'scrub-cap') }}">Scrub cap</a></li>
                    <li><a href="{{ route('products.show', 'white-coat') }}">White coat</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h2>Help &amp; info</h2>
                <ul>
                    <li><a href="{{ route('size-guide') }}">Size guide</a></li>
                    <li><a href="{{ route('shipping') }}">Delivery information</a></li>
                    <li><a href="{{ route('returns.policy') }}">Exchange &amp; returns</a></li>
                    <li><a href="{{ route('contact') }}">Care instructions</a></li>
                    <li><a href="{{ route('faq') }}">FAQs</a></li>
                    <li><a href="{{ route('tracking.create') }}">Track order</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h2>Company</h2>
                <ul>
                    <li><a href="{{ route('about') }}">About us</a></li>
                    <li><a href="{{ route('home') }}#reviews">Reviews</a></li>
                    <li><a href="{{ route('contact') }}">Contact us</a></li>
                    <li><a href="{{ route('privacy') }}">Privacy policy</a></li>
                    <li><a href="{{ route('terms') }}">Terms &amp; conditions</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h2>Customer care</h2>
                <ul class="footer-contact">
                    <li><x-icon name="phone" /> <a href="tel:{{ $location['phone'] }}">{{ $location['phone_display'] }}</a></li>
                    <li><x-icon name="mail" /> <a href="{{ route('contact') }}">Contact the shop</a></li>
                    <li><x-icon name="pin" /> <a href="{{ $location['maps_url'] }}" rel="noopener noreferrer" target="_blank">{{ $location['lines'][2] }}</a></li>
                    <li><x-icon name="clock" /> {{ $location['hours'] }}</li>
                </ul>
                <h2 class="mt-4">Newsletter</h2>
                <p>Subscribe to get special offers, new arrivals and updates.</p>
                <form method="POST" action="{{ route('contact.send') }}" class="newsletter">
                    @csrf
                    <input type="hidden" name="name" value="Newsletter">
                    <input type="hidden" name="subject" value="Newsletter">
                    <input type="hidden" name="message" value="Please add this address to the newsletter.">
                    <label class="visually-hidden" for="newsletter-email">Email</label>
                    <input class="form-control" id="newsletter-email" type="email" name="email" placeholder="Enter your email" required>
                    <button class="btn btn-light" type="submit" aria-label="Subscribe">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-base">
            <form method="POST" action="{{ route('preferences') }}" class="footer-prefs">
                @csrf
                <label class="visually-hidden" for="country_code">Country</label>
                <select class="form-select" id="country_code" name="country_code" onchange="this.form.submit()">
                    <option value="">Country</option>
                    @foreach (($storeCountries ?? []) as $country)
                        <option value="{{ $country->code }}" @selected(session('country_code') === $country->code)>{{ $country->name }}</option>
                    @endforeach
                </select>
                <label class="visually-hidden" for="currency_code">Currency</label>
                <select class="form-select" id="currency_code" name="currency_code" onchange="this.form.submit()">
                    @foreach (($storeCurrencies ?? []) as $currency)
                        <option value="{{ $currency->code }}" @selected(($activeCurrency ?? 'USD') === $currency->code)>{{ $currency->code }}</option>
                    @endforeach
                </select>
            </form>
            <ul class="pay-marks" aria-label="Payment methods">
                <li>Visa</li>
                <li>Mastercard</li>
                <li>easypaisa</li>
                <li>JazzCash</li>
            </ul>
        </div>
    </div>
</footer>
