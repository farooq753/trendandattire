<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->whereDoesntHave('roles')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.customers.index', ['customers' => $customers]);
    }

    public function show(User $user): View
    {
        abort_if($user->isStaff() && ! auth()->user()->hasRole('super-admin'), 403);

        return view('admin.customers.show', [
            'customer' => $user->load(['orders' => fn ($query) => $query->latest('placed_at')->limit(20), 'addresses']),
        ]);
    }
}
