@php
    $lineItems = $lineItems ?? [];
@endphp

<details class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm print:hidden">
    <summary class="cursor-pointer font-medium text-slate-800">Vendor quotation lines (optional)</summary>
    <p class="mt-2 text-xs text-slate-500">Fill when the vendor returns pricing. Row numbers match request lines above.</p>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full border-collapse border border-slate-200 text-sm">
            <thead class="bg-white text-xs font-semibold uppercase text-slate-600">
            <tr>
                <th class="border border-slate-200 px-2 py-2">#</th>
                <th class="border border-slate-200 px-2 py-2">Compliance</th>
                <th class="border border-slate-200 px-2 py-2">Unit price</th>
                <th class="border border-slate-200 px-2 py-2">Quote lead time</th>
                <th class="border border-slate-200 px-2 py-2">Warranty</th>
            </tr>
            </thead>
            <tbody id="rfq-quotation-body">
            @foreach ($lineItems as $index => $row)
                <tr class="rfq-quotation-row">
                    <td class="rfq-quotation-index border border-slate-200 px-2 py-2 font-mono text-xs">{{ $index + 1 }}</td>
                    <td class="border border-slate-200 p-0">
                        <input type="text" name="items[{{ $index }}][compliance]" value="{{ $row['compliance'] ?? '' }}"
                               class="admin-filter-control w-full rounded-none border-0">
                    </td>
                    <td class="border border-slate-200 p-0">
                        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}" min="0" step="0.01"
                               class="rfq-unit admin-filter-control w-full rounded-none border-0">
                    </td>
                    <td class="border border-slate-200 p-0">
                        <input type="text" name="items[{{ $index }}][quote_lead_time]" value="{{ $row['quote_lead_time'] ?? '' }}"
                               class="admin-filter-control w-full rounded-none border-0">
                    </td>
                    <td class="border border-slate-200 p-0">
                        <input type="text" name="items[{{ $index }}][warranty]" value="{{ $row['warranty'] ?? '' }}"
                               class="admin-filter-control w-full rounded-none border-0">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <template id="rfq-quotation-template">
        <tr class="rfq-quotation-row">
            <td class="rfq-quotation-index border border-slate-200 px-2 py-2 font-mono text-xs"></td>
            <td class="border border-slate-200 p-0">
                <input type="text" data-name="compliance" class="admin-filter-control w-full rounded-none border-0">
            </td>
            <td class="border border-slate-200 p-0">
                <input type="number" data-name="unit_price" min="0" step="0.01" class="rfq-unit admin-filter-control w-full rounded-none border-0">
            </td>
            <td class="border border-slate-200 p-0">
                <input type="text" data-name="quote_lead_time" class="admin-filter-control w-full rounded-none border-0">
            </td>
            <td class="border border-slate-200 p-0">
                <input type="text" data-name="warranty" class="admin-filter-control w-full rounded-none border-0">
            </td>
        </tr>
    </template>
</details>
