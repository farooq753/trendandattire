<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · Trend & Attire</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="container-fluid admin-shell">
    <div class="row">
        <aside class="col-lg-2 admin-nav p-3">
            <a class="brand-mark text-white d-block mb-3" href="{{ route('home') }}">Trend &amp; Attire</a>
            @php $user = auth()->user(); @endphp
            @if ($user->hasPermission('dashboard.view'))<a href="{{ route('admin.dashboard') }}">Dashboard</a>@endif
            @if ($user->hasPermission('products.manage'))<a href="{{ route('admin.products.index') }}">Products</a>@endif
            @if ($user->hasPermission('inventory.manage'))<a href="{{ route('admin.inventory.index') }}">Inventory</a>@endif
            @if ($user->hasPermission('orders.view'))<a href="{{ route('admin.orders.index') }}">Orders</a>@endif
            @if ($user->hasPermission('production.manage'))<a href="{{ route('admin.production.index') }}">Production</a>@endif
            @if ($user->hasPermission('customers.view'))<a href="{{ route('admin.customers.index') }}">Customers</a>@endif
            @if ($user->hasPermission('coupons.manage'))<a href="{{ route('admin.coupons.index') }}">Coupons</a>@endif
            @if ($user->hasPermission('banners.manage'))<a href="{{ route('admin.banners.index') }}">Banners</a>@endif
            @if ($user->hasPermission('returns.manage'))<a href="{{ route('admin.returns.index') }}">Returns</a>@endif
            @if ($user->hasPermission('reports.view'))<a href="{{ route('admin.reports.index') }}">Reports</a>@endif
            @if ($user->hasPermission('settings.manage'))<a href="{{ route('admin.settings.edit') }}">Settings</a>@endif
        </aside>
        <div class="col-lg-10 py-4" id="main">
            @include('components.alert')
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
