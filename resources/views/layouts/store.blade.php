<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title ?? 'Trend & Attire')</title>
    <meta name="description" content="@yield('description', $description ?? 'Trend & Attire sells ready-made scrub suits for international orders.')">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('components.local-business')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body @class(['page-home' => request()->routeIs('home')])>
    <a class="skip-link" href="#main">Skip to content</a>
    @include('components.site-header')
    <main id="main" @class(['py-4' => ! request()->routeIs('home')])>
        @if (request()->routeIs('home'))
            @include('components.alert')
            {{ $slot ?? '' }}
            @yield('content')
        @else
            <div class="container">
                @include('components.alert')
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        @endif
    </main>
    @include('components.site-footer')
</body>
</html>
