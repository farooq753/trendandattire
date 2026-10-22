<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['currency_code' => 'USD', 'fulfillment_type' => 'ready', 'gender' => 'unisex', 'garment_type' => 'top']),
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $product = Product::query()->create($this->validated($request));
        $audit->log('product.created', $product, null, $product->only(['name', 'sku', 'regular_price']));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        $product->load(['variants.attributeValues', 'images', 'fabrics', 'customizationOptions.values', 'measurementSchemas']);

        return view('admin.products.form', [
            'product' => $product,
            'attributeValues' => AttributeValue::query()->with('attribute')->get()->groupBy(fn ($value) => $value->attribute->name),
        ]);
    }

    public function update(Request $request, Product $product, AuditLogger $audit): RedirectResponse
    {
        $before = $product->only(['name', 'regular_price', 'sale_price', 'is_published']);
        $product->update($this->validated($request, $product->id));
        $audit->log('product.updated', $product, $before, $product->only(['name', 'regular_price', 'sale_price', 'is_published']));

        return back()->with('status', 'Product saved.');
    }

    public function destroy(Product $product, AuditLogger $audit): RedirectResponse
    {
        $product->update(['is_published' => false]);
        $audit->log('product.unpublished', $product);

        return redirect()->route('admin.products.index')->with('status', 'Product unpublished.');
    }

    public function storeVariant(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:product_variants,sku'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'attribute_value_ids' => ['required', 'array', 'min:1'],
            'attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'stock_on_hand' => ['required', 'integer', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $ids = collect($data['attribute_value_ids'])->map(fn ($id) => (int) $id)->sort()->values();
        $key = $ids->implode('-');

        if ($product->variants()->where('combination_key', $key)->exists()) {
            return back()->withErrors(['attribute_value_ids' => 'That combination already exists.']);
        }

        $variant = $product->variants()->create([
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'combination_key' => $key,
            'regular_price' => $data['regular_price'] ?? null,
            'stock_on_hand' => $data['stock_on_hand'],
            'is_available' => $request->boolean('is_available', true),
        ]);
        $variant->attributeValues()->sync($ids);

        return back()->with('status', 'Variant added.');
    }

    public function storeImage(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt' => ['nullable', 'string', 'max:160'],
        ]);
        $path = $data['image']->store('product-images', 'public');
        $product->images()->create([
            'path' => '/storage/'.$path,
            'alt' => $data['alt'] ?? $product->name,
            'is_primary' => $product->images()->doesntExist(),
        ]);

        return back()->with('status', 'Image uploaded.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignore = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($ignore)],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('products', 'slug')->ignore($ignore)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'gender' => ['required', Rule::in(['men', 'women', 'unisex'])],
            'garment_type' => ['required', Rule::in(['top', 'pants', 'set', 'jacket', 'accessory'])],
            'fulfillment_type' => ['required', Rule::in(['ready', 'custom', 'both'])],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['required', 'exists:currencies,code'],
            'fabric_composition' => ['nullable', 'string'],
            'gsm' => ['nullable', 'integer', 'min:1'],
            'stretch' => ['nullable', 'string', 'max:80'],
            'fit' => ['nullable', 'string', 'max:80'],
            'care' => ['nullable', 'string'],
            'origin_country' => ['nullable', 'string', 'size:2'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'production_lead_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'allows_personalization' => ['nullable', 'boolean'],
            'track_inventory' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_published'] = $request->boolean('is_published');
        $data['allows_personalization'] = $request->boolean('allows_personalization');
        $data['track_inventory'] = $request->boolean('track_inventory', true);
        $data['published_at'] = $data['is_published'] ? now() : null;
        $data['sale_price'] = $data['sale_price'] === '' ? null : $data['sale_price'];
        $data['cost_price'] = $data['cost_price'] === '' ? null : ($data['cost_price'] ?? null);

        return $data;
    }
}
