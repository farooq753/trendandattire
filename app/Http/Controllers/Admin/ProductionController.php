<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Models\ProductionJob;
use App\Services\ProductionWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionController extends Controller
{
    public function index(): View
    {
        return view('admin.production.index', [
            'jobs' => ProductionJob::query()->with('orderItem.order')->latest()->paginate(30),
        ]);
    }

    public function update(Request $request, ProductionJob $job, ProductionWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'staff_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $workflow->transition($job, $data['status'], $data['staff_notes'] ?? null);
        } catch (CheckoutException $exception) {
            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Production status updated.');
    }
}
