<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PMS — Procurement Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-50 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ route('landing') }}" class="text-lg font-semibold tracking-tight text-slate-900">PMS</a>
            <nav class="flex items-center gap-4 text-sm font-medium">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto flex min-h-[calc(100vh-4.25rem)] max-w-5xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6">
        <h1 class="text-5xl font-semibold tracking-tight text-slate-900 sm:text-6xl">PMS</h1>
        <p class="mt-4 max-w-md text-lg text-slate-600">Procurement Management System</p>
        <div class="mt-12">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Login
                </a>
            @endauth
        </div>
    </main>
</body>
</html>
