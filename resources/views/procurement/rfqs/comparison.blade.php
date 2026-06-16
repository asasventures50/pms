@extends('layouts.admin')

@section('title', 'Compare quotations — '.$rfq->rfq_number)

@section('content')
    @php
        $columns = $comparison['columns'];
        $rfqItems = $comparison['rfq_items'];
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
            <button type="button" onclick="window.print()"
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
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                <tr class="bg-slate-900 text-left text-xs font-semibold uppercase tracking-wide text-white">
                    <th class="sticky left-0 z-10 min-w-[12rem] border border-slate-700 bg-slate-900 px-3 py-3">Criteria</th>
                    @foreach ($columns as $column)
                        @php
                            $quotation = $column['quotation'];
                        @endphp
                        <th class="min-w-[11rem] border border-slate-700 px-3 py-3 align-top {{ $column['is_selected'] ? 'bg-emerald-800' : '' }}">
                            <div class="font-mono text-[11px] font-normal normal-case tracking-normal text-slate-200">{{ $quotation->quotation_number }}</div>
                            <div class="mt-1 text-sm font-semibold normal-case">{{ $quotation->vendor_company_name ?? $quotation->vendor?->name ?? '—' }}</div>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @if ($column['is_lowest'])
                                    <span class="rounded-full bg-emerald-300 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-950">Lowest</span>
                                @endif
                                @if ($column['is_selected'])
                                    <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-900">Selected</span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($rfqItems as $lineIndex => $line)
                    <tr class="bg-slate-50">
                        <td colspan="{{ $quotationCount + 1 }}" class="border border-slate-200 px-3 py-2 text-xs font-bold uppercase tracking-wide text-slate-700">
                            Line {{ $lineIndex + 1 }} — {{ $line->item ?: 'Item' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Description</td>
                        @foreach ($columns as $column)
                            <td class="border border-slate-200 px-3 py-2 text-slate-800">{{ $line->description ?: '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Quantity</td>
                        @foreach ($columns as $column)
                            <td class="border border-slate-200 px-3 py-2 text-right font-mono">{{ number_format($line->quantity, 3) }} {{ $line->unit ?: '' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Unit price</td>
                        @foreach ($columns as $column)
                            @php
                                $quoteLine = $column['lines_by_rfq_item_id']->get($line->id);
                            @endphp
                            <td class="border border-slate-200 px-3 py-2 text-right font-mono">
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
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Line total (incl. tax)</td>
                        @foreach ($columns as $column)
                            @php
                                $quoteLine = $column['lines_by_rfq_item_id']->get($line->id);
                                $lineGrand = $quoteLine
                                    ? (float) ($quoteLine->total_price ?? 0) + (float) ($quoteLine->tax ?? 0)
                                    : null;
                            @endphp
                            <td class="border border-slate-200 px-3 py-2 text-right font-mono">
                                {{ $lineGrand !== null ? number_format($lineGrand, 2) : '—' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Compliance</td>
                        @foreach ($columns as $column)
                            @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                            <td class="border border-slate-200 px-3 py-2">{{ $quoteLine?->compliance?->label() ?? ($quoteLine?->compliance ?: '—') }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Brand / origin</td>
                        @foreach ($columns as $column)
                            @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                            <td class="border border-slate-200 px-3 py-2">{{ $quoteLine?->brand_origin ?: '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Tax / VAT rate</td>
                        @foreach ($columns as $column)
                            @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                            <td class="border border-slate-200 px-3 py-2 text-right font-mono">
                                {{ $quoteLine?->tax_rate ? number_format((float) $quoteLine->tax_rate, 2).'%' : '—' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Tax amount</td>
                        @foreach ($columns as $column)
                            @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                            <td class="border border-slate-200 px-3 py-2 text-right font-mono">
                                {{ $quoteLine ? number_format((float) $quoteLine->tax, 2) : '—' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Remarks</td>
                        @foreach ($columns as $column)
                            @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                            <td class="border border-slate-200 px-3 py-2 text-xs whitespace-pre-wrap">{{ $quoteLine?->remarks ?: '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Lead time</td>
                        @foreach ($columns as $column)
                            @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                            <td class="border border-slate-200 px-3 py-2">{{ $quoteLine?->lead_time ?: '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Warranty</td>
                        @foreach ($columns as $column)
                            @php $quoteLine = $column['lines_by_rfq_item_id']->get($line->id); @endphp
                            <td class="border border-slate-200 px-3 py-2">{{ $quoteLine?->warranty ?: '—' }}</td>
                        @endforeach
                    </tr>
                @endforeach

                <tr class="bg-slate-100">
                    <td class="sticky left-0 z-10 border border-slate-300 bg-slate-100 px-3 py-3 font-bold text-slate-900">Grand total</td>
                    @foreach ($columns as $column)
                        @php $total = (float) ($column['quotation']->grand_total ?? 0); @endphp
                        <td class="border border-slate-300 px-3 py-3 text-right font-mono text-base font-bold {{ $column['is_lowest'] ? 'bg-emerald-50 text-emerald-900' : '' }} {{ $column['is_selected'] ? 'ring-2 ring-inset ring-emerald-500' : '' }}">
                            {{ number_format($total, 2) }}
                        </td>
                    @endforeach
                </tr>
                <tr>
                    <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Payment method</td>
                    @foreach ($columns as $column)
                        <td class="border border-slate-200 px-3 py-2">{{ $column['quotation']->payment_method ?: '—' }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-2 font-medium text-slate-600">Notes</td>
                    @foreach ($columns as $column)
                        <td class="border border-slate-200 px-3 py-2 whitespace-pre-wrap text-xs">{{ $column['quotation']->notes ?: '—' }}</td>
                    @endforeach
                </tr>
                @if ($canSelect && $quotationCount > 0)
                    <tr class="print:hidden">
                        <td class="sticky left-0 z-10 border border-slate-200 bg-white px-3 py-3 font-medium text-slate-600">Choose quotation</td>
                        @foreach ($columns as $column)
                            @php $quotation = $column['quotation']; @endphp
                            <td class="border border-slate-200 px-3 py-3 text-center {{ $column['is_selected'] ? 'bg-emerald-50' : '' }}">
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
                </tbody>
            </table>
        </div>
    @endif
@endsection
