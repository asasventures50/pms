@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Dashboard</h1>
        <p class="mt-1 text-sm text-slate-600">Signed in as {{ auth()->user()->name }}.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('vendors.index') }}"
           class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
            <h2 class="text-base font-semibold text-slate-900">Vendors</h2>
            <p class="mt-2 text-sm text-slate-600">Manage vendor records and procurement profiles.</p>
        </a>
        <a href="{{ route('categories.index') }}"
           class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow">
            <h2 class="text-base font-semibold text-slate-900">Categories</h2>
            <p class="mt-2 text-sm text-slate-600">Manage categories and subcategories.</p>
        </a>
    </div>
@endsection
