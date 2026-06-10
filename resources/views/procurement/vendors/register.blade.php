@extends('layouts.public-form')

@section('title', 'Register as Vendor')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Register as Vendor</h1>
        <p class="mt-2 text-sm text-slate-600">
            Complete the form below to submit your company profile for review. No account login is required.
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
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Submit registration
            </button>
        </div>
    </form>
@endsection
