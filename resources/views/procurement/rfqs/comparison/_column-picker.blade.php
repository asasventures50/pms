@if ($columns->count() >= 2)
    <div id="comparison-column-picker"
         class="comparison-column-picker mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm print:hidden">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Choose quotations to compare</h2>
                <p class="mt-1 text-xs text-slate-600">
                    Select at least two quotations, then compare or print. This only affects the view and printout — nothing is deleted.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button"
                        id="comparison-apply-btn"
                        class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover">
                    Compare quotations
                </button>
                <button type="button"
                        id="comparison-print-btn"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    Print comparison
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            @foreach ($columns as $column)
                @php $quotation = $column['quotation']; @endphp
                <label class="inline-flex cursor-pointer items-start gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm hover:border-slate-300">
                    <input type="checkbox"
                           class="comparison-picker-checkbox mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                           value="{{ $quotation->id }}"
                           checked>
                    <span>
                        <span class="block font-mono text-xs text-slate-600">{{ $quotation->quotation_number }}</span>
                        <span class="block font-medium text-slate-900">{{ $quotation->vendor_company_name ?? $quotation->vendor?->name ?? '—' }}</span>
                    </span>
                </label>
            @endforeach
        </div>

        <p id="comparison-picker-feedback" class="mt-3 hidden text-sm" role="status" aria-live="polite"></p>
    </div>
@endif
