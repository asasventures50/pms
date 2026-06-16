@php
    $relatedRfqs = $relatedRfqs ?? collect();
@endphp

@if ($relatedRfqs->isNotEmpty())
    <section class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-6 text-sm shadow-sm print:hidden">
        <h3 class="font-semibold text-slate-900">Vendor quotations</h3>
        <p class="mt-1 text-xs text-slate-600">RFQs created from this procurement request. Compare vendor offers and choose the preferred quotation.</p>
        <ul class="mt-4 space-y-3">
            @foreach ($relatedRfqs as $relatedRfq)
                <li class="flex flex-col gap-2 rounded-lg border border-indigo-100 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-mono font-medium text-slate-900">{{ $relatedRfq->rfq_number }}</p>
                        <p class="mt-1 text-xs text-slate-600">
                            {{ $relatedRfq->vendor_quotations_count }} quotation{{ $relatedRfq->vendor_quotations_count === 1 ? '' : 's' }}
                            @if ($relatedRfq->selectedVendorQuotation)
                                · Selected: <span class="font-mono text-emerald-800">{{ $relatedRfq->selectedVendorQuotation->quotation_number }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if (auth()->user()->canViewQuotationComparison($relatedRfq) && $relatedRfq->vendor_quotations_count > 0)
                            <a href="{{ route('rfqs.comparison.show', $relatedRfq) }}"
                               class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                Compare &amp; choose
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('rfqs.view'))
                            <a href="{{ route('rfqs.show', $relatedRfq) }}#vendor-quotations"
                               class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 hover:bg-slate-50">
                                View RFQ
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
