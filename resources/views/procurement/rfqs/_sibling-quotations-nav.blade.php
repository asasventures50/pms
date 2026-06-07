@php
    $quotations = $rfq->vendorQuotations ?? collect();
    $currentId = $currentQuotationId ?? null;
    $canAddQuotation = $canAddQuotation ?? false;
@endphp

@if ($quotations->isNotEmpty() || $canAddQuotation)
    <nav class="rounded-lg border border-slate-200 bg-slate-50 p-3 print:hidden" aria-label="Quotations for this RFQ">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            {{ $quotations->count() }} quotation{{ $quotations->count() === 1 ? '' : 's' }} for this RFQ
        </p>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            @foreach ($quotations as $sibling)
                @php
                    $isCurrent = $currentId !== null && (int) $sibling->id === (int) $currentId;
                    $vendorLabel = $sibling->vendor_company_name ?? $sibling->vendor?->name ?? 'Vendor';
                @endphp
                @if ($isCurrent)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300 bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-950"
                          title="{{ $vendorLabel }}">
                        <span class="font-mono">{{ $sibling->quotation_number }}</span>
                        <span class="text-emerald-800">· {{ number_format($sibling->grand_total ?? 0, 2) }}</span>
                    </span>
                @elseif (auth()->user()->hasPermission('vendor-quotations.view'))
                    <a href="{{ route('rfqs.quotations.show', [$rfq, $sibling]) }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-700 hover:border-slate-300 hover:bg-slate-50"
                       title="{{ $vendorLabel }}">
                        <span class="font-mono">{{ $sibling->quotation_number }}</span>
                        <span class="text-slate-500">· {{ number_format($sibling->grand_total ?? 0, 2) }}</span>
                    </a>
                @endif
            @endforeach
            @if ($canAddQuotation && $rfq->items->isNotEmpty())
                <a href="{{ route('rfqs.quotations.create', $rfq) }}"
                   class="inline-flex items-center rounded-full border border-dashed border-emerald-400 bg-white px-3 py-1 text-xs font-semibold text-emerald-800 hover:border-emerald-500 hover:bg-emerald-50">
                    + Add another
                </a>
            @endif
        </div>
    </nav>
@endif
