<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login — PMS</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-brand-ink antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-md items-center justify-between px-4 py-3.5 sm:max-w-lg">
            <a href="{{ route('landing') }}" class="text-lg font-semibold tracking-tight text-brand-ink">PMS</a>
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-brand">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Home
            </a>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4.5rem)] flex-col items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card">
            <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-5">
                <h1 class="text-lg font-semibold text-brand-ink">Sign in</h1>
                <p class="mt-1 text-sm text-slate-600">Procurement Management System</p>
            </div>
            <div class="px-6 py-6">
                {{ $slot }}
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
