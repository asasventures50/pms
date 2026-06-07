@extends('layouts.admin')

@section('title', 'Edit vendor quotation')

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit vendor quotation</h1>
            <p class="mt-1 text-sm text-slate-600">
                {{ $quotation->quotation_number }} · RFQ <span class="font-mono">{{ $rfq->rfq_number }}</span>
            </p>
        </div>
        <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">View quotation</a>
    </div>

    <form action="{{ route('rfqs.quotations.update', [$rfq, $quotation]) }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('procurement.vendor-quotations._form', [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'nextCode' => $quotation->quotation_number,
            'selectedVendor' => $selectedVendor,
            'vendorSelectOptions' => $vendorSelectOptions,
            'lineItems' => $lineItems,
            'complianceOptions' => $complianceOptions,
            'documentTypes' => $documentTypes,
        ])

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Update quotation</button>
            <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
