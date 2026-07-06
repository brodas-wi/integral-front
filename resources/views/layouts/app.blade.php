<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-banners-url" content="{{ route('api.banners.active') }}">
    <meta name="api-announcements-url" content="{{ route('api.announcements.for-page') }}">
    <meta name="api-assets-url" content="{{ route('api.assets.active') }}">
    <meta name="api-scripts-url" content="{{ route('api.scripts.active') }}">
    <meta name="map-locations-url" content="{{ route('api.map-locations') }}">
    <meta name="app-url" content="{{ config('app.url') }}">
    <meta name="storage-url" content="{{ config('app.storage_url', config('app.url') . '/storage') }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css'])
    <title>@yield('title', config('app.name'))</title>

    @yield('meta')

    @vite(['resources/css/app.css', 'resources/css/fonts/poppins.css', 'resources/js/app.js'])

    @yield('page-styles')
    @yield('navbar-styles')
    @yield('footer-styles')

    @stack('head')
</head>

<body>
    @yield('navbar')

    <main id="main-content">
        @yield('content')
    </main>

    @yield('footer')

    @yield('page-scripts')

    @stack('scripts')
</body>

</html>
