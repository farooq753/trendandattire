<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use App\Services\CatalogQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private CatalogQuery $catalog) {}

    public function index(Request $request): View
    {
        return $this->render($request, 'Shop', [], true);
    }

    public function gender(Request $request, string $gender): View
    {
        $titles = ['men' => 'Men', 'women' => 'Women', 'unisex' => 'Unisex'];

        return $this->render($request, $titles[$gender].' scrub suits', ['gender' => $gender]);
    }

    public function garment(Request $request, string $garment): View
    {
        $titles = [
            'top' => 'Tops',
            'pants' => 'Pants',
            'set' => 'Sets',
            'jacket' => 'Jackets and lab coats',
            'accessory' => 'Accessories',
        ];

        return $this->render($request, $titles[$garment], ['garment_type' => $garment]);
    }

    public function arrivals(Request $request): View
    {
        return $this->render($request, 'New arrivals', ['new' => true]);
    }

    public function bestSellers(Request $request): View
    {
        return $this->render($request, 'Best sellers', ['sort' => $request->input('sort', 'best_selling')]);
    }

    public function sale(Request $request): View
    {
        return $this->render($request, 'Sale', ['sale' => true]);
    }

    public function search(Request $request): View
    {
        return $this->render($request, 'Search', []);
    }

    /**
     * @param  array<string, mixed>  $locked
     */
    private function render(Request $request, string $title, array $locked, bool $withAddons = false): View
    {
        $filters = array_merge($request->only([
            'q', 'category', 'size', 'color', 'collar', 'fabric', 'fit', 'fulfillment',
            'min_price', 'max_price', 'availability', 'min_rating', 'sort', 'gender', 'garment_type',
        ]), $locked);

        return view('catalog.index', [
            'title' => $title,
            'products' => $this->catalog->listing($filters)->paginate(12)->withQueryString(),
            'filters' => $filters,
            'attributes' => ProductAttribute::query()->where('is_filterable', true)->with('values')->orderBy('sort_order')->get(),
            'activeValues' => AttributeValue::query()->with('attribute')->get(),
            'addons' => $withAddons
                ? $this->catalog->listing(['addons_only' => true])->get()->sortBy(fn ($product) => (float) $product->regular_price)->values()
                : collect(),
        ]);
    }
}
