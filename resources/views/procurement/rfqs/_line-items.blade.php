<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Request details &amp; vendor quotation</h2>
            <p class="mt-1 text-xs text-slate-500">Fill request columns; vendor completes quotation columns.</p>
        </div>
        <button type="button" id="rfq-add-line"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Add row
        </button>
    </div>

    @error('items')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
<div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm" id="rfq-lines-table">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th colspan="5" class="border-b border-slate-200 px-2 py-2 text-center">Request details</th>
                <th colspan="5" class="border-b border-l border-slate-200 px-2 py-2 text-center">Vendor quotation</th>
                <th class="w-12"></th>
            </tr>
            <tr>
                <th class="px-2 py-2">Item</th>
                <th class="px-2 py-2 min-w-[10rem]">Description</th>
                <th class="px-2 py-2 w-24">Qty</th>
                <th class="px-2 py-2 w-20">Unit</th>
                <th class="px-2 py-2 w-28">Lead time</th>
                <th class="border-l border-slate-200 px-2 py-2 w-28">Compliance</th>
                <th class="px-2 py-2 w-28">Unit price</th>
                <th class="px-2 py-2 w-28">Total</th>
                <th class="px-2 py-2 w-28">Lead time</th>
                <th class="px-2 py-2 w-28">Warranty</th>
                <th></th>
            </tr>
            </thead>
            <tbody id="rfq-lines-body">
            @foreach ($lineItems as $index => $row)
                <tr class="rfq-line-row border-t border-slate-100">
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][item]" value="{{ $row['item'] ?? '' }}"
                               class="admin-filter-control w-full font-mono text-xs">
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][description]" value="{{ $row['description'] ?? '' }}" required
                               class="admin-filter-control w-full">
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="number" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" min="0" step="0.001"
                               class="rfq-qty admin-filter-control w-full" required>
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][unit]" value="{{ $row['unit'] ?? '' }}"
                               class="admin-filter-control w-full">
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][request_lead_time]" value="{{ $row['request_lead_time'] ?? '' }}"
                               class="admin-filter-control w-full">
                    </td>
                    <td class="border-l border-slate-100 px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][compliance]" value="{{ $row['compliance'] ?? '' }}"
                               class="admin-filter-control w-full">
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}" min="0" step="0.01"
                               class="rfq-unit admin-filter-control w-full">
                    </td>
                    <td class="px-2 py-2 align-top">
                        <span class="rfq-line-total block rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 text-right font-mono text-xs">0.00</span>
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][quote_lead_time]" value="{{ $row['quote_lead_time'] ?? '' }}"
                               class="admin-filter-control w-full">
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][warranty]" value="{{ $row['warranty'] ?? '' }}"
                               class="admin-filter-control w-full">
                    </td>
                    <td class="px-2 py-2 align-top text-center">
                        <button type="button" class="rfq-remove-line text-xs font-medium text-red-700">×</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="border-t-2 border-slate-200 bg-slate-50">
                <td colspan="7" class="px-2 py-3 text-right text-sm font-semibold text-slate-900">Grand Total:</td>
                <td class="px-2 py-3 text-right font-mono text-sm font-semibold" id="rfq-grand-total">0.00</td>
                <td colspan="3"></td>
            </tr>
            </tfoot>
        </table>
    </div>

    <template id="rfq-line-template">
        <tr class="rfq-line-row border-t border-slate-100">
            <td class="px-2 py-2 align-top"><input type="text" data-name="item" class="admin-filter-control w-full font-mono text-xs"></td>
            <td class="px-2 py-2 align-top"><input type="text" data-name="description" class="admin-filter-control w-full" required></td>
            <td class="px-2 py-2 align-top"><input type="number" data-name="quantity" value="1" min="0" step="0.001" class="rfq-qty admin-filter-control w-full" required></td>
            <td class="px-2 py-2 align-top"><input type="text" data-name="unit" class="admin-filter-control w-full"></td>
            <td class="px-2 py-2 align-top"><input type="text" data-name="request_lead_time" class="admin-filter-control w-full"></td>
            <td class="border-l border-slate-100 px-2 py-2 align-top"><input type="text" data-name="compliance" class="admin-filter-control w-full"></td>
            <td class="px-2 py-2 align-top"><input type="number" data-name="unit_price" min="0" step="0.01" class="rfq-unit admin-filter-control w-full"></td>
            <td class="px-2 py-2 align-top"><span class="rfq-line-total block rounded-lg border border-slate-200 bg-slate-50 px-2 py-2 text-right font-mono text-xs">0.00</span></td>
            <td class="px-2 py-2 align-top"><input type="text" data-name="quote_lead_time" class="admin-filter-control w-full"></td>
            <td class="px-2 py-2 align-top"><input type="text" data-name="warranty" class="admin-filter-control w-full"></td>
            <td class="px-2 py-2 align-top text-center"><button type="button" class="rfq-remove-line text-xs font-medium text-red-700">×</button></td>
        </tr>
    </template>
</section>
