<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — PMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full text-slate-900 antialiased">
    <div class="min-h-full">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <nav class="flex flex-wrap items-center gap-4 text-sm" aria-label="Main">
                    @if (request()->routeIs('dashboard'))
                        <span class="inline-block cursor-default border-b-2 border-black px-2 pb-1 font-semibold text-slate-900" aria-current="page">Dashboard</span>
                    @else
                        <a href="{{ route('dashboard') }}"
                           class="inline-block border-b-2 border-transparent px-2 pb-1 font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900">
                            Dashboard
                        </a>
                    @endif
                    @if (request()->routeIs('vendors.*'))
                        <span class="inline-block cursor-default border-b-2 border-black px-2 pb-1 font-semibold text-slate-900" aria-current="page">Vendors</span>
                    @else
                        <a href="{{ route('vendors.index') }}"
                           class="inline-block border-b-2 border-transparent px-2 pb-1 font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900">
                            Vendors
                        </a>
                    @endif
                    @if (request()->routeIs('categories.*'))
                        <span class="inline-block cursor-default border-b-2 border-black px-2 pb-1 font-semibold text-slate-900" aria-current="page">Categories</span>
                    @else
                        <a href="{{ route('categories.index') }}"
                           class="inline-block border-b-2 border-transparent px-2 pb-1 font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900">
                            Categories
                        </a>
                    @endif
                    @if (request()->routeIs('locations.*') || request()->routeIs('countries.*') || request()->routeIs('cities.*'))
                        <span class="inline-block cursor-default border-b-2 border-black px-2 pb-1 font-semibold text-slate-900" aria-current="page">Locations</span>
                    @else
                        <a href="{{ route('locations.index') }}"
                           class="inline-block border-b-2 border-transparent px-2 pb-1 font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900">
                            Locations
                        </a>
                    @endif
                </nav>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="hidden max-w-[12rem] truncate text-xs text-slate-500 sm:inline" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                    <form id="admin-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                    <button type="button"
                            id="admin-logout-open"
                            class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 shadow-sm transition hover:border-red-300 hover:bg-red-100 hover:text-red-700">
                        Logout
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                    <p class="font-medium">Please fix the following:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- Logout dialog: uses display:none when closed (no overlay blocking the page). --}}
    <div id="admin-logout-dialog"
         class="fixed inset-0 z-50 hidden"
         role="dialog"
         aria-modal="true"
         aria-labelledby="admin-logout-dialog-title"
         aria-hidden="true">
            <div id="admin-logout-dialog-panel"
                 class="w-full max-w-md rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xl shadow-slate-900/10 ring-1 ring-slate-900/5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 ring-1 ring-red-100" aria-hidden="true">
                        <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <h2 id="admin-logout-dialog-title" class="text-lg font-semibold tracking-tight text-slate-900">Logout</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Are you sure you want to logout?</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                    <button type="button"
                            id="admin-logout-dialog-cancel"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                            form="admin-logout-form"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        Logout
                    </button>
                </div>
            </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dialog = document.getElementById('admin-logout-dialog');
            const openBtn = document.getElementById('admin-logout-open');
            const cancelBtn = document.getElementById('admin-logout-dialog-cancel');
            if (!dialog || !openBtn) {
                return;
            }

            const openClasses = ['flex', 'items-center', 'justify-center', 'bg-slate-900/45', 'p-4', 'sm:p-6'];

            function openDialog() {
                dialog.classList.remove('hidden');
                openClasses.forEach(function (c) {
                    dialog.classList.add(c);
                });
                dialog.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            }

            function closeDialog() {
                openClasses.forEach(function (c) {
                    dialog.classList.remove(c);
                });
                dialog.classList.add('hidden');
                dialog.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
                openBtn.focus();
            }

            openBtn.addEventListener('click', openDialog);
            cancelBtn?.addEventListener('click', closeDialog);
            const panel = document.getElementById('admin-logout-dialog-panel');
            dialog.addEventListener('click', function (e) {
                if (panel && !panel.contains(e.target)) {
                    closeDialog();
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !dialog.classList.contains('hidden')) {
                    closeDialog();
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
