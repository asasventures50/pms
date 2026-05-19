@php
    $po = $po ?? null;
    $userDefaultCurrency = auth()->user()?->defaultCurrencyCode();
    $currencyRaw = trim((string) old('currency_code', $po?->currency_code ?? $userDefaultCurrency ?? ''));
    $currencyCode = $currencyRaw !== '' ? strtoupper($currencyRaw) : null;
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 pb-3 sm:flex-row sm:items-end sm:justify-between">
        <h2 class="text-base font-semibold text-slate-900">Line items</h2>
        <div class="flex flex-wrap items-end gap-3">
            <div class="w-28">
                <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
                <input type="text" name="currency_code" id="currency_code" maxlength="3"
                       value="{{ $currencyCode ?? '' }}"
                       data-user-default-currency="{{ $userDefaultCurrency ?? '' }}"
                       placeholder="{{ $userDefaultCurrency ?: '—' }}"
                       class="admin-filter-control mt-1 uppercase @error('currency_code') border-red-500 @enderror"
                       autocomplete="off">
                <p class="mt-1 text-xs text-slate-500">
                    @if ($userDefaultCurrency)
                    Set your code (e.g. USD, SAR) for line items.
                    @else
                        Set your code (e.g. USD, SAR) for line items.
                    @endif
                </p>
                @error('currency_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="button" id="po-add-line"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Add row
            </button>
        </div>
    </div>

    @error('items')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm" id="po-lines-table">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2 w-24">Item</th>
                <th class="px-2 py-2">Item or service description</th>
                <th class="px-2 py-2 w-28">Quantity</th>
                <th class="px-2 py-2 w-32">
                    <span data-po-price-label data-po-price-label-base="Price per unit">Price per unit{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                </th>
                <th class="px-2 py-2 w-32">
                    <span data-po-price-label data-po-price-label-base="Total">Total{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                </th>
                <th class="px-2 py-2 w-16"></th>
            </tr>
            </thead>
            <tbody id="po-lines-body">
            @foreach ($lineItems as $index => $row)
                <tr class="po-line-row border-t border-slate-100">
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][item]" value="{{ $row['item'] ?? '' }}"
                               class="admin-filter-control w-full font-mono text-xs">
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="items[{{ $index }}][description]" value="{{ $row['description'] ?? '' }}" required
                               class="admin-filter-control w-full @error('items.'.$index.'.description') border-red-500 @enderror">
                        @error('items.'.$index.'.description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="number" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" min="0" step="0.001"
                               class="po-qty admin-filter-control w-full" required>
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? 0 }}" min="0" step="0.01"
                               class="po-unit admin-filter-control w-full" required>
                    </td>
                    <td class="px-2 py-2 align-top">
                        <span class="po-line-total block rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right font-mono text-sm">0.00</span>
                    </td>
                    <td class="px-2 py-2 align-top text-center">
                        <button type="button" class="po-remove-line text-xs font-medium text-red-700 hover:text-red-900" title="Remove row">×</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="border-t-2 border-slate-200 bg-slate-50">
                <td colspan="4" class="px-2 py-3 text-right text-sm font-semibold text-slate-900">
                    <span data-po-price-label data-po-price-label-base="Grand Total:">Grand Total:{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                </td>
                <td class="px-2 py-3 text-right font-mono text-sm font-semibold text-slate-900" id="po-grand-total">0.00</td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>

    <template id="po-line-template">
        <tr class="po-line-row border-t border-slate-100">
            <td class="px-2 py-2 align-top">
                <input type="text" data-name="item" class="admin-filter-control w-full font-mono text-xs">
            </td>
            <td class="px-2 py-2 align-top">
                <input type="text" data-name="description" class="admin-filter-control w-full" required>
            </td>
            <td class="px-2 py-2 align-top">
                <input type="number" data-name="quantity" value="1" min="0" step="0.001" class="po-qty admin-filter-control w-full" required>
            </td>
            <td class="px-2 py-2 align-top">
                <input type="number" data-name="unit_price" value="0" min="0" step="0.01" class="po-unit admin-filter-control w-full" required>
            </td>
            <td class="px-2 py-2 align-top">
                <span class="po-line-total block rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right font-mono text-sm">0.00</span>
            </td>
            <td class="px-2 py-2 align-top text-center">
                <button type="button" class="po-remove-line text-xs font-medium text-red-700 hover:text-red-900">×</button>
            </td>
        </tr>
    </template>
</section>
