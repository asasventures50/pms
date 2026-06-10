@extends('layouts.public-form')

@section('title', __('vendor_registration.page_title'))

@section('content')
    <div class="mb-8 text-center">
        <h1 class="public-form-title text-2xl font-semibold tracking-tight">{{ __('vendor_registration.page_heading') }}</h1>
        <p class="mt-2 text-sm text-slate-600">
            {{ __('vendor_registration.page_subtitle') }}
        </p>
    </div>

    <form id="vendor-form" action="{{ route('vendor-registration.store') }}" method="post" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @include('procurement.vendors._form', [
            'vendor' => null,
            'mode' => 'public_register',
            'categories' => $categories,
            'countries' => $countries,
            'defaultCountryId' => $defaultCountryId,
            'defaultCityId' => $defaultCityId,
            'suggestedVendorCode' => null,
        ])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="public-form-submit-btn">
                {{ __('vendor_registration.submit_registration') }}
            </button>
        </div>
    </form>
@endsection
