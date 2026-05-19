@php
    $lineItems = $lineItems ?? [];
    $preserveQuotationFields = ! config('procurement.rfq.show_extended_form_fields');
@endphp

<section class="mt-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Request details</h3>
        <button type="button" id="rfq-add-line"
                class="rounded border border-slate-400 bg-white px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 print:hidden">
            Add row
        </button>
    </div>

    @error('items')<p class="mt-2 text-sm text-red-600 print:hidden">{{ $message }}</p>@enderror

    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full border-collapse border border-slate-900 text-sm text-slate-900" id="rfq-lines-table">
            <thead>
            <tr class="bg-white">
                <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase">Item</th>
                <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase">Item or service description</th>
                <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase w-24">Quantity</th>
                <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase w-24">Unit</th>
                <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase w-32">Lead time</th>
                <th class="w-10 border border-slate-900 print:hidden"></th>
            </tr>
            </thead>
            <tbody id="rfq-lines-body">
            @foreach ($lineItems as $index => $row)
                <tr class="rfq-line-row">
                    <td class="border border-slate-900 p-0 align-top">
                        <input type="text" name="items[{{ $index }}][item]" value="{{ $row['item'] ?? '' }}"
                               class="rfq-doc-input w-full px-2 py-2 font-mono text-sm">
                    </td>
                    <td class="border border-slate-900 p-0 align-top">
                        <input type="text" name="items[{{ $index }}][description]" value="{{ $row['description'] ?? '' }}" required
                               class="rfq-doc-input w-full px-2 py-2 text-sm">
                    </td>
                    <td class="border border-slate-900 p-0 align-top">
                        <input type="number" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" min="0" step="0.001"
                               class="rfq-doc-input rfq-qty w-full px-2 py-2 text-sm" required>
                    </td>
                    <td class="border border-slate-900 p-0 align-top">
                        <input type="text" name="items[{{ $index }}][unit]" value="{{ $row['unit'] ?? '' }}"
                               class="rfq-doc-input w-full px-2 py-2 text-sm">
                    </td>
                    <td class="border border-slate-900 p-0 align-top">
                        <input type="text" name="items[{{ $index }}][request_lead_time]" value="{{ $row['request_lead_time'] ?? '' }}"
                               class="rfq-doc-input w-full px-2 py-2 text-sm">
                    </td>
                    <td class="border border-slate-900 px-1 py-2 text-center align-middle print:hidden">
                        <button type="button" class="rfq-remove-line text-sm font-medium text-red-700 hover:text-red-900" title="Remove row">×</button>
                        @if ($preserveQuotationFields)
                            <input type="hidden" name="items[{{ $index }}][compliance]" value="{{ $row['compliance'] ?? '' }}">
                            <input type="hidden" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}">
                            <input type="hidden" name="items[{{ $index }}][quote_lead_time]" value="{{ $row['quote_lead_time'] ?? '' }}">
                            <input type="hidden" name="items[{{ $index }}][warranty]" value="{{ $row['warranty'] ?? '' }}">
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <template id="rfq-line-template">
        <tr class="rfq-line-row">
            <td class="border border-slate-900 p-0 align-top">
                <input type="text" data-name="item" class="rfq-doc-input w-full px-2 py-2 font-mono text-sm">
            </td>
            <td class="border border-slate-900 p-0 align-top">
                <input type="text" data-name="description" class="rfq-doc-input w-full px-2 py-2 text-sm" required>
            </td>
            <td class="border border-slate-900 p-0 align-top">
                <input type="number" data-name="quantity" value="1" min="0" step="0.001" class="rfq-doc-input rfq-qty w-full px-2 py-2 text-sm" required>
            </td>
            <td class="border border-slate-900 p-0 align-top">
                <input type="text" data-name="unit" class="rfq-doc-input w-full px-2 py-2 text-sm">
            </td>
            <td class="border border-slate-900 p-0 align-top">
                <input type="text" data-name="request_lead_time" class="rfq-doc-input w-full px-2 py-2 text-sm">
            </td>
            <td class="border border-slate-900 px-1 py-2 text-center align-middle print:hidden">
                <button type="button" class="rfq-remove-line text-sm font-medium text-red-700 hover:text-red-900" title="Remove row">×</button>
                @if ($preserveQuotationFields)
                    <input type="hidden" data-name="compliance" value="">
                    <input type="hidden" data-name="unit_price" value="">
                    <input type="hidden" data-name="quote_lead_time" value="">
                    <input type="hidden" data-name="warranty" value="">
                @endif
            </td>
        </tr>
    </template>
</section>
