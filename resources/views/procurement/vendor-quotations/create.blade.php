@extends('layouts.admin')

@section('title', 'New vendor quotation')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Vendor quotation</h1>
        <p class="mt-1 text-sm text-slate-600">
            RFQ <span class="font-mono">{{ $rfq->rfq_number }}</span>
        </p>
    </div>

    <form action="{{ route('rfqs.quotations.store', $rfq) }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('procurement.vendor-quotations._form', [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'nextCode' => $nextCode,
            'vendors' => $vendors,
            'lineItems' => $lineItems,
            'complianceOptions' => $complianceOptions,
            'documentTypes' => $documentTypes,
        ])

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Save quotation</button>
            <a href="{{ route('rfqs.show', $rfq) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
