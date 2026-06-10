@extends('layouts.public-form')

@section('title', __('vendor_registration.thanks_title'))

@section('content')
    <div class="mx-auto max-w-lg rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <div class="public-form-success-icon mx-auto flex h-12 w-12 items-center justify-center rounded-full">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <h1 class="public-form-title mt-4 text-xl font-semibold">{{ __('vendor_registration.thanks_heading') }}</h1>
        @if ($vendorName)
            <p class="mt-2 text-sm text-slate-600">
                {{ __('vendor_registration.thanks_with_name', ['name' => $vendorName]) }}
            </p>
        @else
            <p class="mt-2 text-sm text-slate-600">{{ __('vendor_registration.thanks_generic') }}</p>
        @endif
        <p class="mt-3 text-sm text-slate-600">
            {{ __('vendor_registration.thanks_review') }}
        </p>
        <a href="{{ route('vendor-registration.create') }}"
           class="public-form-submit-btn mt-6 inline-flex items-center justify-center px-4 py-2">
            {{ __('vendor_registration.back_to_registration') }}
        </a>
    </div>
@endsection
