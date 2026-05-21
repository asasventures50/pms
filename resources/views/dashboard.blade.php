@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-600">
            Signed in as {{ auth()->user()->name }}
            @if (auth()->user()->roles->isNotEmpty())
                · {{ auth()->user()->roles->pluck('label')->join(', ') }}
            @endif
        </p>
    </div>

    @if (auth()->user()->hasPermission('procurement-requests.view') || auth()->user()->hasPermission('purchase-orders.view'))
        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Procurement</h2>
            <p class="mt-1 text-sm text-slate-600">Open procurement requests or purchase orders.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                @if (auth()->user()->hasPermission('procurement-requests.view'))
                    <a href="{{ route('procurement-requests.index') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                        PR — Procurement Requests
                    </a>
                @endif
                @if (auth()->user()->hasPermission('purchase-orders.view'))
                    <a href="{{ route('purchase-orders.index') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                        PO — Purchase Orders
                    </a>
                @endif
            </div>
        </section>
    @endif

    @if (auth()->user()->hasPermission('projects.view'))
        <section class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Projects &amp; zones</h2>
            <p class="mt-1 text-sm text-slate-600">Manage projects and their zones for procurement line items.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('projects.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50">
                    View projects
                </a>
                @if (auth()->user()->hasPermission('projects.create'))
                    <a href="{{ route('projects.create') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                        Add project + zones
                    </a>
                @endif
            </div>
        </section>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @if (auth()->user()->hasPermission('vendors.view'))
            <a href="{{ route('vendors.index') }}"
               class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
                <h2 class="text-base font-semibold text-slate-900">Vendors</h2>
                <p class="mt-2 text-sm text-slate-600">Manage vendor records and procurement profiles.</p>
            </a>
        @endif
        @if (auth()->user()->hasPermission('categories.view'))
            <a href="{{ route('categories.index') }}"
               class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
                <h2 class="text-base font-semibold text-slate-900">Categories</h2>
                <p class="mt-2 text-sm text-slate-600">Manage categories and subcategories.</p>
            </a>
        @endif
        @if (auth()->user()->hasPermission('rfqs.view'))
            <a href="{{ route('rfqs.index') }}"
               class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
                <h2 class="text-base font-semibold text-slate-900">RFQs</h2>
                <p class="mt-2 text-sm text-slate-600">Request for quotation forms.</p>
            </a>
        @endif
        @if (auth()->user()->hasPermission('users.view'))
            <a href="{{ route('users.index') }}"
               class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
                <h2 class="text-base font-semibold text-slate-900">Users</h2>
                <p class="mt-2 text-sm text-slate-600">Manage accounts and role assignments.</p>
            </a>
        @endif
        @if (auth()->user()->hasPermission('roles.view'))
            <a href="{{ route('roles.index') }}"
               class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
                <h2 class="text-base font-semibold text-slate-900">Roles</h2>
                <p class="mt-2 text-sm text-slate-600">Configure roles and permissions.</p>
            </a>
        @endif
    </div>
@endsection
