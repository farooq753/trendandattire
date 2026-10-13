<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Models\ProductReturn;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(): View
    {
        return view('admin.returns.index', [
            'returns' => ProductReturn::query()->with('order')->latest()->paginate(25),
        ]);
    }

    public function update(Request $request, ProductReturn $productReturn, ReturnService $returns): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'staff_notes' => ['nullable', 'string', 'max:2000'],
            'restock' => ['nullable', 'boolean'],
        ]);

        try {
            $returns->transition($productReturn, $data['status'], $data['staff_notes'] ?? null, $request->boolean('restock'));
        } catch (CheckoutException $exception) {
            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Return updated.');
    }
}
