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
        @if (auth()->user()->hasPermission('purchase-orders.view'))
            <a href="{{ route('purchase-orders.index') }}"
               class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
                <h2 class="text-base font-semibold text-slate-900">Purchase Orders</h2>
                <p class="mt-2 text-sm text-slate-600">Create and track purchase orders.</p>
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
