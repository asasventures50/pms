@extends('layouts.admin')

@section('title', 'Compare quotations — '.$rfq->rfq_number)

@section('content')
    @include('procurement.rfqs.comparison._print-styles')

    @php
        $columns = $comparison['columns'];
        $rfqItems = $comparison['rfq_items'];
        $quotationRows = $comparison['quotation_rows'];
        $lineRows = $comparison['line_rows'];
        $quotationCount = $columns->count();
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Quotation comparison</h1>
            <p class="mt-1 text-sm text-slate-600">
                RFQ <span class="font-mono font-medium">{{ $rfq->rfq_number }}</span>
                @if ($rfq->selectedVendorQuotation)
                    · Selected: <span class="font-mono font-medium text-emerald-800">{{ $rfq->selectedVendorQuotation->quotation_number }}</span>
                @endif
            </p>
            @if ($rfq->selected_at)
                <p class="mt-1 text-xs text-slate-500">
                    Chosen by {{ $rfq->selectedBy?->name ?? '—' }} on {{ $rfq->selected_at->format('Y-m-d H:i') }}
                </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->hasPermission('rfqs.view'))
                <a href="{{ route('rfqs.show', $rfq) }}#vendor-quotations"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    Back to RFQ
                </a>
            @endif
            @if ($canSelect && $rfq->selected_vendor_quotation_id)
                <form method="post" action="{{ route('rfqs.comparison.clear-selection', $rfq) }}"
                      onsubmit="return confirm('Clear the selected quotation for this RFQ?');">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-950 hover:bg-amber-100">
                        Clear selection
                    </button>
                </form>
            @endif
            <button type="button"
                    @if ($quotationCount >= 2) data-comparison-print-trigger @else onclick="window.print()" @endif
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Print
            </button>
        </div>
    </div>

    @if ($quotationCount < 2)
        <div class="mx-auto max-w-3xl rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 print:hidden">
            Add at least two vendor quotations to compare side by side.
            @if (auth()->user()->hasPermission('rfqs.view'))
                <a href="{{ route('rfqs.show', $rfq) }}#vendor-quotations" class="ml-1 font-medium underline">Go to RFQ</a>
            @endif
        </div>
    @endif

    @if ($quotationCount === 0)
        <div class="mx-auto max-w-3xl rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-600">
            No vendor quotations recorded for this RFQ yet.
        </div>
    @else
        @include('procurement.rfqs.comparison._column-picker', ['columns' => $columns])

        <article class="comparison-document mx-auto max-w-full border-2 border-slate-900 bg-white p-4 text-slate-900 shadow-sm sm:p-6 print:shadow-none">
            @include('procurement.rfqs.comparison._comparison-document-header', ['rfq' => $rfq])

            <div class="mt-4 grid gap-2 border-b border-slate-900 pb-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <span class="text-xs font-medium uppercase text-slate-500">RFQ No.</span>
                    <p class="font-mono font-semibold">{{ $rfq->rfq_number }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase text-slate-500">Issue date</span>
                    <p>{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase text-slate-500">Quotations compared</span>
                    <p id="comparison-visible-count">{{ $quotationCount }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium uppercase text-slate-500">Printed on</span>
                    <p>{{ now()->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            <div class="comparison-table-wrapper mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm print:mt-4">
                <table class="comparison-table min-w-full border-collapse text-sm"
                       style="--comparison-quotation-count: {{ max($quotationCount, 1) }};">
                    <thead>
                    <tr class="comparison-header-accent text-xs font-semibold uppercase tracking-wide text-slate-800">
                        <th class="comparison-criteria-cell sticky left-0 z-10 border px-3 py-3">Criteria</th>
                        @foreach ($columns as $column)
                            @php
                                $quotation = $column['quotation'];
                            @endphp
                            <th class="comparison-data-cell border px-3 py-3 align-middle {{ $column['is_selected'] ? 'comparison-col-selected' : '' }}"
                                data-comparison-quotation="{{ $quotation->id }}">
                                <div class="font-mono text-[11px] font-normal normal-case tracking-normal text-slate-600">{{ $quotation->quotation_number }}</div>
                                <div class="mt-1 text-sm font-semibold normal-case">{{ $quotation->vendor_company_name ?? $quotation->vendor?->name ?? '—' }}</div>
                                <div class="mt-2 flex flex-wrap justify-center gap-1">
                                    @if ($column['is_lowest'])
                                        <span class="comparison-badge-lowest rounded-full bg-emerald-300 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-950">Lowest</span>
                                    @endif
                                    @if ($column['is_selected'])
                                        <span class="comparison-badge-selected rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-900">Selected</span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rfqItems as $lineIndex => $line)
                        @php
                            $lineRowVisibility = $lineRows->get($line->id, []);
                        @endphp
                        <tr class="comparison-line-header bg-slate-50">
                            <td colspan="{{ $quotationCount + 1 }}" class="comparison-data-cell border border-slate-200 px-3 py-2 text-xs font-bold uppercase tracking-wide text-slate-700">
                                Line {{ $lineIndex + 1 }} — {{ $line->item ?: 'Item' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Description</td>
                            @foreach ($columns as $column)
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2 text-slate-800 whitespace-pre-wrap text-start" dir="auto">{{ $line->description ?: '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Quantity</td>
                            @foreach ($columns as $column)
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2 font-mono">{{ number_format($line->quantity, 3) }} {{ $line->unit ?: '' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Unit price</td>
                            @foreach ($columns as $column)
                                @php
                                    $quoteLine = $column['lines_by_rfq_item_id']->get($line->id);
                                @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2 font-mono">
                                    @if ($quoteLine?->unit_price !== null)
                                        {{ number_format((float) $quoteLine->unit_price, 2) }}
                                        @if ($quoteLine->currency)
                                            <span class="text-xs text-slate-500">{{ $quoteLine->currency }}</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Line total (incl. tax)</td>
                            @foreach ($columns as $column)
                                @php
                                    $quoteLine = $column['lines_by_rfq_item_id']->get($line->id);
                                    $lineGrand = $quoteLine
                                        ? (float) ($quoteLine->total_price ?? 0) + (float) ($quoteLine->tax ?? 0)
                                        : null;
                                @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2 font-mono">
                                    {{ $lineGrand !== null ? number_format($lineGrand, 2) : '—' }}
                                </td>
                            @endforeach
                        </tr>
                        @if ($lineRowVisibility['compliance'] ?? false)
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Compliance</td>
                            @foreach ($columns as $column)
                                @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2">{{ $quoteLine?->compliance?->label() ?? ($quoteLine?->compliance ?: '—') }}</td>
                            @endforeach
                        </tr>
                        @endif
                        @if ($lineRowVisibility['brand_origin'] ?? false)
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Brand / origin</td>
                            @foreach ($columns as $column)
                                @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2">{{ $quoteLine?->brand_origin ?: '—' }}</td>
                            @endforeach
                        </tr>
                        @endif
                        @if ($lineRowVisibility['tax_rate'] ?? false)
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Tax / VAT rate</td>
                            @foreach ($columns as $column)
                                @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2 font-mono">
                                    {{ $quoteLine?->tax_rate ? number_format((float) $quoteLine->tax_rate, 2).'%' : '—' }}
                                </td>
                            @endforeach
                        </tr>
                        @endif
                        @if ($lineRowVisibility['tax_amount'] ?? false)
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Tax amount</td>
                            @foreach ($columns as $column)
                                @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2 font-mono">
                                    {{ $quoteLine ? number_format((float) $quoteLine->tax, 2) : '—' }}
                                </td>
                            @endforeach
                        </tr>
                        @endif
                        @if ($lineRowVisibility['remarks'] ?? false)
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Remarks</td>
                            @foreach ($columns as $column)
                                @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2 text-xs break-words text-start" dir="auto">{{ $quoteLine?->remarks ?: '—' }}</td>
                            @endforeach
                        </tr>
                        @endif
                        @if ($lineRowVisibility['lead_time'] ?? false)
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Lead time</td>
                            @foreach ($columns as $column)
                                @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2">{{ $quoteLine?->lead_time ?: '—' }}</td>
                            @endforeach
                        </tr>
                        @endif
                        @if ($lineRowVisibility['warranty'] ?? false)
                        <tr>
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Warranty</td>
                            @foreach ($columns as $column)
                                @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-2">{{ $quoteLine?->warranty ?: '—' }}</td>
                            @endforeach
                        </tr>
                        @endif
                    @endforeach

                    <tr class="comparison-grand-total-row bg-slate-100">
                        <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-300 bg-slate-100 px-3 py-3 font-bold text-slate-900">Grand total</td>
                        @foreach ($columns as $column)
                            @php $total = (float) ($column['quotation']->grand_total ?? 0); @endphp
                            <td class="comparison-data-cell comparison-lowest-total border border-slate-300 px-3 py-3 font-mono text-base font-bold {{ $column['is_lowest'] ? 'bg-emerald-50 text-emerald-900' : '' }} {{ $column['is_selected'] ? 'ring-2 ring-inset ring-emerald-500' : '' }}">
                                {{ number_format($total, 2) }}
                            </td>
                        @endforeach
                    </tr>
                    @if ($quotationRows['payment_method'])
                    <tr>
                        <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Payment method</td>
                        @foreach ($columns as $column)
                            <td class="comparison-data-cell border border-slate-200 px-3 py-2">{{ $column['quotation']->payment_method ?: '—' }}</td>
                        @endforeach
                    </tr>
                    @endif
                    @if ($quotationRows['notes'])
                    <tr>
                        <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Notes</td>
                        @foreach ($columns as $column)
                            <td class="comparison-data-cell border border-slate-200 px-3 py-2 text-xs break-words whitespace-pre-wrap text-start" dir="auto">{{ $column['quotation']->notes ?: '—' }}</td>
                        @endforeach
                    </tr>
                    @endif
                    @if ($canSelect && $quotationCount > 0)
                        <tr class="comparison-screen-select-row">
                            <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-white px-3 py-3 font-medium text-slate-600">Choose quotation</td>
                            @foreach ($columns as $column)
                                @php $quotation = $column['quotation']; @endphp
                                <td class="comparison-data-cell border border-slate-200 px-3 py-3 {{ $column['is_selected'] ? 'bg-emerald-50' : '' }}">
                                    @if ($column['is_selected'])
                                        <span class="inline-flex items-center rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white">Selected</span>
                                    @else
                                        <form method="post" action="{{ route('rfqs.comparison.select', $rfq) }}"
                                              onsubmit="return confirm('Select {{ $quotation->quotation_number }} as the preferred quotation?');">
                                            @csrf
                                            <input type="hidden" name="vendor_quotation_id" value="{{ $quotation->id }}">
                                            <button type="submit"
                                                    class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                                Select this quote
                                            </button>
                                        </form>
                                    @endif
                                    @if (auth()->user()->hasPermission('vendor-quotations.view'))
                                        <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}"
                                           class="mt-2 inline-block text-xs font-medium text-slate-600 hover:text-slate-900">
                                            View full quotation
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                    <tr class="comparison-signoff-select-row">
                        <td class="comparison-criteria-cell sticky left-0 z-10 border border-slate-200 bg-slate-50 px-3 py-4 font-medium text-slate-700">Selected quotation</td>
                        @foreach ($columns as $column)
                            <td class="comparison-data-cell border border-slate-200 bg-slate-50 px-3 py-4">
                                <span class="comparison-print-checkbox" aria-hidden="true"></span>
                            </td>
                        @endforeach
                    </tr>
                    </tbody>
                </table>

                <div class="comparison-signoff-notes border border-t-0 border-slate-200 bg-slate-50/50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Manager notes</p>
                    <div class="comparison-handwritten-notes-box mt-3" aria-hidden="true"></div>
                </div>
            </div>

            @include('procurement.rfqs.comparison._supporting-documents', ['columns' => $columns])
        </article>

        @include('procurement.rfqs.comparison._comparison-filter', ['columns' => $columns])
    @endif
@endsection
