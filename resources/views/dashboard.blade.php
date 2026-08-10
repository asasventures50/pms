@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $isSuperAdmin = $isSuperAdmin ?? auth()->user()->isSuperAdmin();
        $open = (int) ($prStatusCounts['open'] ?? 0);
        $inProgress = (int) ($prStatusCounts['in_progress'] ?? 0);
        $closed = (int) ($prStatusCounts['closed'] ?? 0);
        $total = max((int) ($prStatusTotal ?? 0), 1);
        $openPct = round(($open / $total) * 100, 2);
        $inProgressPct = round(($inProgress / $total) * 100, 2);
        $closedPct = round(($closed / $total) * 100, 2);
        $donutGradient = ($prStatusTotal ?? 0) > 0
            ? "conic-gradient(#1e293b 0 {$openPct}%, #64748b {$openPct}% ".($openPct + $inProgressPct)."%, #cbd5e1 ".($openPct + $inProgressPct)."% 100%)"
            : 'conic-gradient(#e2e8f0 0 100%)';
    @endphp

    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-brand-ink">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-600">
            Signed in as {{ auth()->user()->name }}
            @if (auth()->user()->roles->isNotEmpty())
                · {{ auth()->user()->roles->pluck('label')->join(', ') }}
            @endif
        </p>
    </div>

    @if ($isSuperAdmin)
        {{-- Stat widgets (super admin only) --}}
        <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="admin-card flex items-start gap-4 !p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand" aria-hidden="true">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Open PRs</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-brand-ink">{{ number_format($stats['open_prs']) }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Draft, submitted &amp; received</p>
                </div>
            </div>

            <div class="admin-card flex items-start gap-4 !p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-accent/15 text-brand" aria-hidden="true">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v3"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Active projects</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-brand-ink">{{ number_format($stats['active_projects']) }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Currently in progress</p>
                </div>
            </div>

            <div class="admin-card flex items-start gap-4 !p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700" aria-hidden="true">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Unpaid POs</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-brand-ink">{{ number_format($stats['unpaid_pos']) }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">Unpaid or partial</p>
                </div>
            </div>

            <div class="admin-card flex items-start gap-4 !p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700" aria-hidden="true">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64M12 6.75V3m0 3.75A2.25 2.25 0 0114.25 9h1.5A2.25 2.25 0 0118 6.75V3m-6 3.75A2.25 2.25 0 009.75 9h-1.5A2.25 2.25 0 016 6.75V3"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Vendors</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-brand-ink">{{ number_format($stats['vendors']) }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">{{ number_format($stats['invoices']) }} invoices total</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Main + sidebar --}}
    <div @class(['grid gap-6', 'lg:grid-cols-10' => $isSuperAdmin])>
        <div @class(['space-y-6', 'lg:col-span-7' => $isSuperAdmin])>
            @if ($isSuperAdmin || auth()->user()->canAccessProcurementRequestFlow())
                <div @class(['grid gap-6', 'sm:grid-cols-2' => $isSuperAdmin && auth()->user()->canAccessProcurementRequestFlow()])>
                    @if ($isSuperAdmin)
                        <section class="admin-card">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand" aria-hidden="true">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/>
                                    </svg>
                                </span>
                                <h2 class="text-base font-semibold text-brand-ink">Request status</h2>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">Procurement requests by status</p>

                            <div class="mt-5 flex items-center gap-6">
                                <div class="relative h-28 w-28 shrink-0 rounded-full" style="background: {{ $donutGradient }};" role="img" aria-label="Request status chart">
                                    <div class="absolute inset-[22%] flex items-center justify-center rounded-full bg-white">
                                        <div class="text-center">
                                            <p class="text-lg font-semibold tabular-nums leading-none text-brand-ink">{{ number_format($prStatusTotal) }}</p>
                                            <p class="mt-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500">Total</p>
                                        </div>
                                    </div>
                                </div>
                                <ul class="min-w-0 flex-1 space-y-2.5 text-sm">
                                    <li class="flex items-center justify-between gap-3">
                                        <span class="inline-flex items-center gap-2 text-slate-700">
                                            <span class="h-2.5 w-2.5 rounded-full bg-brand" aria-hidden="true"></span>
                                            Open
                                        </span>
                                        <span class="font-semibold tabular-nums text-brand-ink">{{ number_format($open) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between gap-3">
                                        <span class="inline-flex items-center gap-2 text-slate-700">
                                            <span class="h-2.5 w-2.5 rounded-full bg-brand-accent" aria-hidden="true"></span>
                                            In progress
                                        </span>
                                        <span class="font-semibold tabular-nums text-brand-ink">{{ number_format($inProgress) }}</span>
                                    </li>
                                    <li class="flex items-center justify-between gap-3">
                                        <span class="inline-flex items-center gap-2 text-slate-700">
                                            <span class="h-2.5 w-2.5 rounded-full bg-slate-300" aria-hidden="true"></span>
                                            Closed
                                        </span>
                                        <span class="font-semibold tabular-nums text-brand-ink">{{ number_format($closed) }}</span>
                                    </li>
                                </ul>
                            </div>
                        </section>
                    @endif

                    @if (auth()->user()->canAccessProcurementRequestFlow())
                        <section class="admin-card flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand" aria-hidden="true">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.189C3.24 5.38 3 5.77 3 6.195V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                                    </svg>
                                </span>
                                <h2 class="text-base font-semibold text-brand-ink">
                                    {{ auth()->user()->canViewAllProcurementRequestFlows() ? 'Request tracking' : 'My request tracking' }}
                                </h2>
                            </div>
                            <p class="mt-1 flex-1 text-sm text-slate-600">
                                @if (auth()->user()->canViewAllProcurementRequestFlows())
                                    See where every procurement request stands — RFQ, quotations, PO, and invoice.
                                @else
                                    See where each of your procurement requests stands — RFQ, quotations, PO, and invoice.
                                @endif
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('procurement-requests.my-flow') }}" class="admin-btn-secondary">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                    </svg>
                                    Visual Tracking Flow
                                </a>
                            </div>
                        </section>
                    @endif
                </div>
            @endif

            @php
                $canSeeProcurement = auth()->user()->hasPermission('procurement-requests.view')
                    || auth()->user()->hasPermission('rfqs.view')
                    || auth()->user()->hasPermission('rfq-terms.view')
                    || auth()->user()->hasPermission('purchase-orders.view')
                    || auth()->user()->hasPermission('invoices.create')
                    || auth()->user()->hasPermission('schedule-of-works.create')
                    || auth()->user()->hasPermission('quick-receipts.view')
                    || auth()->user()->hasPermission('quick-receipts.view-own')
                    || auth()->user()->hasPermission('quick-receipts.create')
                    || auth()->user()->hasPermission('quick-receipts.approve');

                $locationsUrl = \Illuminate\Support\Facades\Route::has('locations.index')
                    ? route('locations.index')
                    : (\Illuminate\Support\Facades\Route::has('countries.index') ? route('countries.index') : null);

                $canSeeMasterData = auth()->user()->hasPermission('projects.view')
                    || auth()->user()->hasPermission('vendors.view')
                    || auth()->user()->hasPermission('categories.view')
                    || (auth()->user()->hasPermission('locations.view') && $locationsUrl);

                $canSeeAdmin = auth()->user()->hasPermission('users.view')
                    || auth()->user()->hasPermission('roles.view');
            @endphp

            @if ($canSeeProcurement)
                <section class="admin-card">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand" aria-hidden="true">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                            </svg>
                        </span>
                        <h2 class="text-base font-semibold text-brand-ink">Procurement</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">Full purchase cycle: PR → RFQ → PO → Invoice, plus supporting documents.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if (auth()->user()->hasPermission('procurement-requests.view'))
                            <a href="{{ route('procurement-requests.index') }}" class="admin-btn-primary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                                PR — Procurement Requests
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('rfqs.view') || auth()->user()->hasPermission('rfq-terms.view'))
                            <a href="{{ route('rfqs.index') }}" class="admin-btn-primary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                </svg>
                                RFQs
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('purchase-orders.view'))
                            <a href="{{ route('purchase-orders.index') }}" class="admin-btn-primary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18A2.25 2.25 0 0020.25 16.5V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                </svg>
                                PO — Purchase Orders
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('invoices.create'))
                            <a href="{{ route('invoices.index') }}" class="admin-btn-secondary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                                </svg>
                                Invoices
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('schedule-of-works.create'))
                            <a href="{{ route('schedule-of-works.index') }}" class="admin-btn-secondary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                </svg>
                                Schedule of Works
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('quick-receipts.view') || auth()->user()->hasPermission('quick-receipts.view-own') || auth()->user()->hasPermission('quick-receipts.create') || auth()->user()->hasPermission('quick-receipts.approve'))
                            <a href="{{ route('quick-receipts.index') }}" class="admin-btn-secondary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Quick Receipts
                            </a>
                        @endif
                    </div>
                </section>
            @endif

            @if ($canSeeMasterData)
                <section class="admin-card">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand" aria-hidden="true">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                        </span>
                        <h2 class="text-base font-semibold text-brand-ink">Master data</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">Projects, vendors, categories, and locations used across procurement.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @if (auth()->user()->hasPermission('projects.view'))
                            <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a.75.75 0 01.75-.75h3a.75.75 0 01.75.75v3"/>
                                    </svg>
                                    <h3 class="text-sm font-semibold text-brand-ink">Projects &amp; zones</h3>
                                </div>
                                <p class="mt-1 text-xs text-slate-600">Manage projects and zones for line items.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('projects.index') }}" class="admin-btn-secondary !px-3 !py-1.5 text-xs">View projects</a>
                                    @if (auth()->user()->hasPermission('projects.create'))
                                        <a href="{{ route('projects.create') }}" class="admin-btn-primary !px-3 !py-1.5 text-xs">
                                            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                            Add project
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if (auth()->user()->hasPermission('vendors.view'))
                            <a href="{{ route('vendors.index') }}" class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-brand/30 hover:shadow-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64M12 6.75V3m0 3.75A2.25 2.25 0 0114.25 9h1.5A2.25 2.25 0 0118 6.75V3m-6 3.75A2.25 2.25 0 009.75 9h-1.5A2.25 2.25 0 016 6.75V3"/>
                                    </svg>
                                    <h3 class="text-sm font-semibold text-brand-ink">Vendors</h3>
                                </div>
                                <p class="mt-1 text-xs text-slate-600">Vendor records and procurement profiles.</p>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('categories.view'))
                            <a href="{{ route('categories.index') }}" class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-brand/30 hover:shadow-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                                    </svg>
                                    <h3 class="text-sm font-semibold text-brand-ink">Categories</h3>
                                </div>
                                <p class="mt-1 text-xs text-slate-600">Categories and subcategories catalog.</p>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('locations.view') && $locationsUrl)
                            <a href="{{ $locationsUrl }}" class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-brand/30 hover:shadow-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                    </svg>
                                    <h3 class="text-sm font-semibold text-brand-ink">Locations</h3>
                                </div>
                                <p class="mt-1 text-xs text-slate-600">Countries and cities for vendor coverage.</p>
                            </a>
                        @endif
                    </div>
                </section>
            @endif

            @if ($canSeeAdmin)
                <section class="admin-card">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand" aria-hidden="true">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.174.1.31.292.31.502v.086c0 .21-.136.402-.31.502-.332.183-.582.495-.645.87l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a.522.522 0 01-.31-.502v-.086c0-.21.136-.402.31-.502.332-.183.582-.495.644-.87l.213-1.28z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <h2 class="text-base font-semibold text-brand-ink">Administration</h2>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">Accounts, roles, and access control.</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if (auth()->user()->hasPermission('users.view'))
                            <a href="{{ route('users.index') }}" class="admin-btn-secondary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                </svg>
                                Users
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('roles.view'))
                            <a href="{{ route('roles.index') }}" class="admin-btn-secondary">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                                </svg>
                                Roles
                            </a>
                        @endif
                    </div>
                </section>
            @endif
        </div>

        @if ($isSuperAdmin)
            {{-- Activity sidebar (super admin only) --}}
            <aside class="lg:col-span-3">
                <section class="admin-card sticky top-6">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand" aria-hidden="true">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <h2 class="text-base font-semibold text-brand-ink">Activity</h2>
                        </div>
                        <a href="{{ route('activity-logs.index') }}" class="text-xs font-medium text-brand hover:text-brand-hover">View all</a>
                    </div>

                    @if ($recentActivity->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">No recent activity yet.</p>
                    @else
                        <ul class="mt-4 space-y-3">
                            @foreach ($recentActivity as $log)
                                <li class="border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                                    <p class="line-clamp-2 text-sm font-medium text-slate-800">
                                        {{ $log->description ?? $log->action }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $log->user?->name ?? 'System' }}
                                        · {{ $log->created_at?->diffForHumans() }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </aside>
        @endif
    </div>
@endsection
