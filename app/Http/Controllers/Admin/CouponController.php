<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', ['coupons' => Coupon::query()->latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', ['coupon' => new Coupon(['type' => 'percent', 'is_active' => true])]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $coupon = Coupon::query()->create($this->validated($request));
        $audit->log('coupon.created', $coupon, null, $coupon->only(['code', 'type', 'value']));

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', ['coupon' => $coupon]);
    }

    public function update(Request $request, Coupon $coupon, AuditLogger $audit): RedirectResponse
    {
        $before = $coupon->only(['code', 'value', 'is_active']);
        $coupon->update($this->validated($request, $coupon->id));
        $audit->log('coupon.updated', $coupon, $before, $coupon->only(['code', 'value', 'is_active']));

        return back()->with('status', 'Coupon saved.');
    }

    public function destroy(Coupon $coupon, AuditLogger $audit): RedirectResponse
    {
        $coupon->update(['is_active' => false]);
        $audit->log('coupon.deactivated', $coupon);

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon deactivated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?int $ignore = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('coupons', 'code')->ignore($ignore)],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'exists:currencies,code'],
            'min_subtotal' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
