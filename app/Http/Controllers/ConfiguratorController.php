<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\CustomConfigurator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguratorController extends Controller
{
    public function __construct(
        private CustomConfigurator $configurator,
        private CartService $carts,
    ) {}

    public function index(): View
    {
        $products = Product::query()
            ->published()
            ->whereIn('fulfillment_type', ['custom', 'both'])
            ->with('images')
            ->orderBy('name')
            ->get();

        return view('custom.index', ['products' => $products]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published && $product->acceptsCustom(), 404);
        $product->load(['fabrics', 'customizationOptions.values', 'measurementSchemas', 'images']);

        return view('custom.configure', ['product' => $product, 'review' => null]);
    }

    public function store(Request $request, Product $product): RedirectResponse|View
    {
        abort_unless($product->is_published && $product->acceptsCustom(), 404);
        $product->load(['fabrics', 'customizationOptions.values', 'measurementSchemas']);

        $data = $request->validate([
            'fabric_id' => ['nullable', 'integer'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'integer'],
            'sizing_method' => ['required', 'in:standard,custom'],
            'standard_size' => ['nullable', 'string', 'max:8'],
            'measurements' => ['nullable', 'array'],
            'measurements.*.value' => ['nullable', 'string', 'max:12'],
            'measurements.*.unit' => ['nullable', 'in:cm,in'],
            'personalization_type' => ['nullable', 'in:none,text,logo'],
            'personalization_text' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
            'action' => ['required', 'in:review,confirm'],
        ]);

        $logoPath = $request->session()->get('custom_logo.'.$product->id);

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'private');
            $request->session()->put('custom_logo.'.$product->id, $logoPath);
        }

        $input = $data;
        $input['logo_path'] = $logoPath;
        $priced = $this->configurator->price($product, $input);

        if (! $priced['ok']) {
            return back()->withInput()->withErrors($priced['errors']);
        }

        if ($data['action'] === 'review') {
            return view('custom.configure', ['product' => $product, 'review' => $priced, 'input' => $data]);
        }

        $cart = $this->carts->current($request->user(), session('guest_cart'));
        $this->carts->add(
            $cart,
            $product,
            null,
            'custom',
            (int) ($data['quantity'] ?? 1),
            $priced['unit_price'],
            [
                'selections' => $input,
                'lines' => $priced['configuration'],
            ],
            $priced['measurements'],
            $priced['personalization'],
        );
        $request->session()->forget('custom_logo.'.$product->id);

        return redirect()->route('cart.show')->with('status', 'Custom scrub added to cart.');
    }
}
