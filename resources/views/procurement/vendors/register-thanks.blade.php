@extends('layouts.public-form')

@section('title', 'Registration Submitted')

@section('content')
    <div class="mx-auto max-w-lg rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <h1 class="mt-4 text-xl font-semibold text-slate-900">Thank you for registering</h1>
        @if ($vendorName)
            <p class="mt-2 text-sm text-slate-600">
                We received the registration for <span class="font-medium text-slate-900">{{ $vendorName }}</span>.
            </p>
        @else
            <p class="mt-2 text-sm text-slate-600">We received your vendor registration.</p>
        @endif
        <p class="mt-3 text-sm text-slate-600">
            Our procurement team will review your submission and contact you if needed.
        </p>
        <a href="{{ route('landing') }}"
           class="mt-6 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            Back to home
        </a>
    </div>
@endsection
