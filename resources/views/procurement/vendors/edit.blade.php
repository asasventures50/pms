@extends('layouts.admin')

@section('title', 'Edit Vendor')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Vendor</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $vendor->name }} ({{ $vendor->vendor_code }})</p>
        </div>
        <div class="flex flex-wrap gap-4 text-sm font-medium">
            <a href="{{ route('vendors.show', $vendor) }}" class="text-slate-600 hover:text-slate-900">View</a>
            <a href="{{ $listReturnUrl ?? route('vendors.index') }}" class="text-slate-600 hover:text-slate-900">Back to list</a>
        </div>
    </div>

    <form id="vendor-form" action="{{ route('vendors.update', $vendor) }}" method="post" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')
        @if ($listReturnUrl)
            <input type="hidden" name="return" value="{{ $listReturnUrl }}">
        @endif
        @include('procurement.vendors._form', [
            'vendor' => $vendor,
            'mode' => 'edit',
            'categories' => $categories,
            'countries' => $countries,
            'defaultCountryId' => $defaultCountryId,
            'defaultCityId' => $defaultCityId,
            'suggestedVendorCode' => $suggestedVendorCode,
        ])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Update Vendor
            </button>
            <a href="{{ route('vendors.show', $vendor) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
