<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login — PMS</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-md items-center justify-between px-4 py-4 sm:max-w-lg">
            <a href="{{ route('landing') }}" class="text-lg font-semibold tracking-tight text-slate-900">PMS</a>
            <a href="{{ route('landing') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Home</a>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-4.25rem)] flex-col items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h1 class="text-base font-semibold text-slate-900">Sign in</h1>
                <p class="mt-1 text-sm text-slate-600">Procurement Management System</p>
            </div>
            <div class="px-6 py-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
