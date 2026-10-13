<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'banners' => Banner::query()
                ->where('is_active', true)
                ->where('placement', 'home')
                ->where(function ($query) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->orderBy('sort_order')
                ->get(),
            'featured' => Product::query()->published()->where('is_featured', true)->with('images')->latest('published_at')->limit(8)->get(),
            'arrivals' => Product::query()->published()->with('images')->latest('published_at')->limit(4)->get(),
        ]);
    }
}
