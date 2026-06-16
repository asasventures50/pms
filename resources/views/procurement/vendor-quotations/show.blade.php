@extends('layouts.admin')

@section('title', $quotation->quotation_number)

@section('content')
    @php
        $canAddQuotation = auth()->user()->hasPermission('vendor-quotations.create')
            || auth()->user()->hasPermission('rfqs.update');
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $quotation->quotation_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                Vendor quotation for RFQ <a href="{{ route('rfqs.show', $rfq) }}#vendor-quotations" class="font-mono font-medium text-slate-800 hover:underline">{{ $rfq->rfq_number }}</a>
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if ($canAddQuotation && $rfq->items->isNotEmpty())
                <a href="{{ route('rfqs.quotations.create', $rfq) }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800">+ Add another quotation</a>
            @endif
            @if (auth()->user()->hasPermission('vendor-quotations.update'))
                <a href="{{ route('rfqs.quotations.edit', [$rfq, $quotation]) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            @endif
            <a href="{{ route('rfqs.quotations.print', [$rfq, $quotation]) }}" target="_blank" rel="noopener"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Print</a>
            <a href="{{ route('rfqs.show', $rfq) }}#vendor-quotations" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to RFQ</a>
        </div>
    </div>

    <div class="mb-6 print:hidden">
        @include('procurement.rfqs._sibling-quotations-nav', [
            'rfq' => $rfq,
            'currentQuotationId' => $quotation->id,
            'canAddQuotation' => $canAddQuotation,
        ])
    </div>

    <article class="vq-document mx-auto max-w-6xl border-2 border-slate-900 bg-white p-4 text-slate-900 shadow-sm sm:p-6 print:border print:shadow-none">
        @include('procurement.vendor-quotations._document-body', [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'rfqContext' => $rfqContext,
            'buyerCompany' => $buyerCompany ?? null,
            'documentTypes' => $documentTypes,
            'declarations' => $declarations,
        ])
    </article>

    @if (auth()->user()->hasPermission('vendor-quotations.update'))
        <form action="{{ route('rfqs.quotations.destroy', [$rfq, $quotation]) }}" method="post" class="mt-6 print:hidden"
              onsubmit="return confirm('Delete this vendor quotation?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-900">Delete quotation</button>
        </form>
    @endif
@endsection
