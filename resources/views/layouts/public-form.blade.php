<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#E65100">

    <title>@yield('title', 'PMS') — ASAS Ventures</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="public-form-page min-h-screen font-sans text-slate-900 antialiased">
    <header class="public-form-header border-b bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-center px-4 py-5 sm:px-6 lg:px-8">
            <img src="{{ asset('images/po/logo.png') }}" alt="ASAS Ventures" class="public-form-logo h-auto w-auto max-h-20">
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
