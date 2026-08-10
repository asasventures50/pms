@extends('layouts.admin')

@section('title', 'New vendor quotation')

@section('content')
    @php
        $existingCount = $rfq->vendorQuotations->count();
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                {{ $existingCount > 0 ? 'Add another vendor quotation' : 'Add vendor quotation' }}
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                RFQ <a href="{{ route('rfqs.show', $rfq) }}#vendor-quotations" class="font-mono font-medium text-slate-800 hover:underline">{{ $rfq->rfq_number }}</a>
                @if ($existingCount > 0)
                    · quotation {{ $existingCount + 1 }} of this RFQ
                @endif
            </p>
        </div>
        <a href="{{ route('rfqs.show', $rfq) }}#vendor-quotations" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to RFQ</a>
    </div>

    @if ($existingCount > 0)
        <div class="mb-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm text-slate-700">
                This RFQ already has <strong>{{ $existingCount }}</strong> quotation{{ $existingCount === 1 ? '' : 's' }} on file.
                You are adding a new one — update vendor details and pricing as needed.
            </p>
            @include('procurement.rfqs._sibling-quotations-nav', [
                'rfq' => $rfq,
                'canAddQuotation' => false,
            ])
        </div>
    @endif

    <form action="{{ route('rfqs.quotations.store', $rfq) }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('procurement.vendor-quotations._form', [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'nextCode' => $nextCode,
            'selectedVendor' => $selectedVendor,
            'vendorSelectOptions' => $vendorSelectOptions,
            'lineItems' => $lineItems,
            'complianceOptions' => $complianceOptions,
            'documentTypes' => $documentTypes,
            'rfqContext' => $rfqContext,
            'declarations' => $declarations,
        ])

        <div class="flex flex-wrap gap-3 mx-auto max-w-5xl">
            <button type="submit" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-hover">
                Save quotation
            </button>
            <a href="{{ route('rfqs.show', $rfq) }}#vendor-quotations" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
