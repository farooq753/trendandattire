<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title ?? 'Trend & Attire')</title>
    <meta name="description" content="@yield('description', $description ?? 'Trend & Attire designs ready-made and custom-stitched scrub suits for international orders.')">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    @include('components.site-header')
    <main id="main" class="py-4">
        <div class="container">
            @include('components.alert')
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>
    @include('components.site-footer')
</body>
</html>
