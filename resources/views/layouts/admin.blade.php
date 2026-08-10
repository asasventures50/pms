<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — PMS</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full text-slate-900 antialiased">
    <div class="min-h-full">
        <header class="border-b border-slate-200 bg-white print:hidden">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <nav class="flex flex-wrap items-center gap-3 text-sm sm:gap-4" aria-label="Main">
                    {{-- Lean top nav: ops shortcuts only. Master data & admin live on the dashboard. --}}
                    @if (request()->routeIs('dashboard'))
                        <span class="admin-nav-link-active" aria-current="page">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                            Dashboard
                        </span>
                    @else
                        <a href="{{ route('dashboard') }}" class="admin-nav-link">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                            Dashboard
                        </a>
                    @endif
                    @if (auth()->user()->hasPermission('procurement-requests.view'))
                        @if (request()->routeIs('procurement-requests.*'))
                            <span class="admin-nav-link-active" aria-current="page">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                                PR
                            </span>
                        @else
                            <a href="{{ route('procurement-requests.index') }}" class="admin-nav-link">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                                PR
                            </a>
                        @endif
                    @endif
                    @if (auth()->user()->hasPermission('rfqs.view') || auth()->user()->hasPermission('rfq-terms.view'))
                        @if (request()->routeIs('rfqs.*'))
                            <span class="admin-nav-link-active" aria-current="page">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                </svg>
                                RFQs
                            </span>
                        @else
                            <a href="{{ route('rfqs.index') }}" class="admin-nav-link">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                </svg>
                                RFQs
                            </a>
                        @endif
                    @endif
                    @if (auth()->user()->hasPermission('vendors.view'))
                        @if (request()->routeIs('vendors.*'))
                            <span class="admin-nav-link-active" aria-current="page">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64M12 6.75V3m0 3.75A2.25 2.25 0 0114.25 9h1.5A2.25 2.25 0 0118 6.75V3m-6 3.75A2.25 2.25 0 009.75 9h-1.5A2.25 2.25 0 016 6.75V3"/>
                                </svg>
                                Vendors
                            </span>
                        @else
                            <a href="{{ route('vendors.index') }}" class="admin-nav-link">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64M12 6.75V3m0 3.75A2.25 2.25 0 0114.25 9h1.5A2.25 2.25 0 0118 6.75V3m-6 3.75A2.25 2.25 0 009.75 9h-1.5A2.25 2.25 0 016 6.75V3"/>
                                </svg>
                                Vendors
                            </a>
                        @endif
                    @endif
                    @if (auth()->user()->hasPermission('activity-logs.view'))
                        @if (request()->routeIs('activity-logs.*'))
                            <span class="admin-nav-link-active" aria-current="page">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Activity
                            </span>
                        @else
                            <a href="{{ route('activity-logs.index') }}" class="admin-nav-link">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Activity
                            </a>
                        @endif
                    @endif
                </nav>
                <div class="flex shrink-0 items-center gap-2">
                    <span class="hidden max-w-[12rem] truncate text-xs text-slate-500 sm:inline" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                    <form id="admin-logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                    <button type="button"
                            id="admin-logout-open"
                            class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-red-600/80"
                            title="Logout">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                        </svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 print:max-w-none print:px-0 print:py-0">
            @if (session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 print:hidden" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 print:hidden" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 print:hidden" role="alert">
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
         class="fixed inset-0 z-50 hidden print:hidden"
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
