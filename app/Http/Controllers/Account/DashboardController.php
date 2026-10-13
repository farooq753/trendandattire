<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        return view('account.dashboard', [
            'orders' => $user->orders()->latest('placed_at')->limit(5)->get(),
            'addresses' => $user->addresses()->count(),
        ]);
    }
}
