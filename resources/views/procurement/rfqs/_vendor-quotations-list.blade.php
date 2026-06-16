@php
    $quotations = $rfq->vendorQuotations;
    $canAddQuotation = $canAddQuotation ?? false;
    $lowestTotal = $quotations->count() >= 2
        ? $quotations->min(fn ($q) => (float) ($q->grand_total ?? 0))
        : null;
@endphp

@if ($quotations->isNotEmpty())
    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
            <tr>
                <th class="px-3 py-2 text-left">Quotation No.</th>
                <th class="px-3 py-2 text-left">Vendor</th>
                <th class="px-3 py-2 text-right">Grand total</th>
                <th class="px-3 py-2 text-left">Date</th>
                <th class="px-3 py-2 text-right">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($quotations as $quotation)
                @php
                    $total = (float) ($quotation->grand_total ?? 0);
                    $isLowest = $lowestTotal !== null && abs($total - $lowestTotal) < 0.001;
                    $isSelected = (int) ($rfq->selected_vendor_quotation_id ?? 0) === (int) $quotation->id;
                @endphp
                <tr class="{{ $isSelected ? 'bg-emerald-100/70' : ($isLowest ? 'bg-emerald-50/60' : '') }}">
                    <td class="px-3 py-2">
                        <span class="font-mono">{{ $quotation->quotation_number }}</span>
                        @if ($isSelected)
                            <span class="ml-1.5 rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">Selected</span>
                        @elseif ($isLowest)
                            <span class="ml-1.5 rounded-full bg-emerald-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-900">Lowest</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">{{ $quotation->vendor_company_name ?? $quotation->vendor?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($total, 2) }}</td>
                    <td class="px-3 py-2">{{ $quotation->created_at?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-3 py-2 text-right whitespace-nowrap">
                        @if (auth()->user()->hasPermission('vendor-quotations.view'))
                            <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                        @endif
                        @if (auth()->user()->hasPermission('vendor-quotations.update'))
                            <span class="mx-1 text-slate-300">|</span>
                            <a href="{{ route('rfqs.quotations.edit', [$rfq, $quotation]) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if ($canAddQuotation && $rfq->items->isNotEmpty())
        <div class="mt-4 flex flex-col gap-2 border-t border-emerald-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-700">
                Add offers from different vendors or an updated quote from the same vendor to compare.
            </p>
            <a href="{{ route('rfqs.quotations.create', $rfq) }}"
               class="inline-flex shrink-0 items-center justify-center rounded-lg border border-emerald-600 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">
                + Add another quotation
            </a>
        </div>
    @endif
@else
    <div class="mt-4 rounded-lg border-2 border-dashed border-emerald-300 bg-white px-4 py-8 text-center">
        <p class="text-sm font-medium text-slate-900">No vendor quotations yet</p>
        <p class="mt-1 text-sm text-slate-600">
            Record the first vendor offer, then add more to compare prices and terms.
        </p>
        @if ($canAddQuotation && $rfq->items->isNotEmpty())
            <a href="{{ route('rfqs.quotations.create', $rfq) }}"
               class="mt-4 inline-flex items-center justify-center rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">
                + Add first quotation
            </a>
        @elseif ($canAddQuotation)
            <p class="mt-3 text-sm font-medium text-amber-800">Add RFQ line items first (Edit → Request details).</p>
        @endif
    </div>
@endif
