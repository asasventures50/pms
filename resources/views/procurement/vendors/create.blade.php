@extends('layouts.admin')

@section('title', 'Add Vendor')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add Vendor</h1>
            <p class="mt-1 text-sm text-slate-600">Create a new vendor record.</p>
        </div>
        <a href="{{ route('vendors.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to list</a>
    </div>

    <form id="vendor-form" action="{{ route('vendors.store') }}" method="post" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @include('procurement.vendors._form', [
            'vendor' => null,
            'mode' => 'create',
            'categories' => $categories,
            'countries' => $countries,
            'defaultCountryId' => $defaultCountryId,
            'defaultCityId' => $defaultCityId,
            'suggestedVendorCode' => $suggestedVendorCode,
        ])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Save Vendor
            </button>
            <a href="{{ route('vendors.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
