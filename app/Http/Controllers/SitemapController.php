<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            route('home'),
            route('shop'),
            route('men'),
            route('women'),
            route('unisex'),
            route('tops'),
            route('pants'),
            route('sets'),
            route('jackets'),
            route('accessories'),
            route('new-arrivals'),
            route('best-sellers'),
            route('sale'),
            route('custom.index'),
            route('about'),
            route('contact'),
            route('faq'),
            route('shipping'),
            route('returns.policy'),
            route('privacy'),
            route('terms'),
            route('size-guide'),
            route('measurement-guide'),
        ];

        foreach (Product::query()->published()->orderBy('id')->get() as $product) {
            $urls[] = route('products.show', $product);
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /account\nDisallow: /cart\nDisallow: /checkout\nDisallow: /track\nAllow: /\nSitemap: ".route('sitemap')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
