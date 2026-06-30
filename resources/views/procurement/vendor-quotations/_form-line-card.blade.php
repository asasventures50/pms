@php
    $qty = (float) ($row['quantity'] ?? 0);
    $qtyQuoted = $row['quantity_quoted'] ?? $qty;
@endphp

<article class="vq-line-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm" data-quantity="{{ $qty }}">
    <input type="hidden" name="items[{{ $index }}][rfq_item_id]" value="{{ $row['rfq_item_id'] ?? '' }}">

    <div class="border-b border-slate-100 pb-3">
        <p class="text-sm font-semibold text-slate-900">
            Line {{ $index + 1 }}
            <span class="font-mono text-xs font-normal text-slate-500">· {{ $row['item_number'] ?? '—' }}</span>
        </p>
        <p class="mt-1 text-sm text-slate-700">{{ $row['description'] ?? '—' }}</p>
    </div>

    <details class="mt-3 rounded-lg border border-slate-100 bg-slate-50 p-3 text-sm" open>
        <summary class="cursor-pointer text-xs font-semibold uppercase tracking-wide text-slate-600">Requested item (from RFQ)</summary>
        <dl class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-xs text-slate-500">Quantity</dt><dd>{{ number_format($qty, 3) }} {{ $row['unit'] ?? '' }}</dd></div>
            <div><dt class="text-xs text-slate-500">Delivery location</dt><dd>{{ $row['delivery_location'] ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">Required delivery</dt><dd>{{ $row['request_lead_time'] ?? '—' }}</dd></div>
        </dl>
    </details>

    <div class="mt-4 space-y-4">
        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Compliance &amp; offering</h4>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Compliance status</label>
                <select name="items[{{ $index }}][compliance]" class="vq-compliance admin-filter-control mt-1 w-full">
                    <option value="">— Select —</option>
                    @foreach ($complianceOptions as $option)
                        <option value="{{ $option->value }}" @selected(($row['compliance'] ?? '') === $option->value)>{{ $option->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Country of origin</label>
                <input type="text" name="items[{{ $index }}][country_of_origin]" value="{{ $row['country_of_origin'] ?? '' }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Deviation / alternative offered (if not compliant)</label>
                <textarea name="items[{{ $index }}][alternative_if_no]" rows="2"
                          class="admin-form-textarea mt-1 w-full">{{ $row['alternative_if_no'] ?? '' }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Item description (if not compliant)</label>
                <textarea name="items[{{ $index }}][item_description_if_no]" rows="2"
                          class="admin-form-textarea mt-1 w-full">{{ $row['item_description_if_no'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Brand</label>
                <input type="text" name="items[{{ $index }}][brand]" value="{{ $row['brand'] ?? '' }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Model / part number</label>
                <input type="text" name="items[{{ $index }}][model]" value="{{ $row['model'] ?? '' }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
        </div>

        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Pricing</h4>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Unit price</label>
                <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $row['unit_price'] ?? '' }}" min="0" step="0.01"
                       class="vq-unit-price admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
                <input type="text" name="items[{{ $index }}][currency]" value="{{ $row['currency'] ?? '' }}" maxlength="10"
                       class="admin-filter-control mt-1 w-full" placeholder="SAR">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Quantity quoted</label>
                <input type="number" name="items[{{ $index }}][quantity_quoted]" value="{{ $qtyQuoted }}" min="0" step="0.001"
                       class="vq-qty-quoted admin-filter-control mt-1 w-full">
                <p class="mt-1 text-xs text-slate-500">Requested: {{ number_format($qty, 3) }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Line total</label>
                <input type="number" name="items[{{ $index }}][total_price]" value="{{ $row['total_price'] ?? '' }}" min="0" step="0.01"
                       class="vq-total-price admin-filter-control mt-1 w-full">
                <p class="mt-1 text-xs text-slate-500">Auto-calculated from qty × price if left empty</p>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Discount</label>
                <input type="number" name="items[{{ $index }}][discount]" value="{{ $row['discount'] ?? '' }}" min="0" step="0.01"
                       class="vq-discount admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Tax rate (%)</label>
                <input type="number" name="items[{{ $index }}][tax_rate]" value="{{ $row['tax_rate'] ?? '' }}" min="0" max="100" step="0.01"
                       class="vq-tax-rate admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Tax amount</label>
                <input type="number" name="items[{{ $index }}][tax]" value="{{ $row['tax'] ?? '' }}" min="0" step="0.01"
                       class="vq-tax admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery charges</label>
                <input type="number" name="items[{{ $index }}][delivery_charges]" value="{{ $row['delivery_charges'] ?? '' }}" min="0" step="0.01"
                       class="vq-line-delivery admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Installation</label>
                <input type="number" name="items[{{ $index }}][installation]" value="{{ $row['installation'] ?? '' }}" min="0" step="0.01"
                       class="vq-line-installation admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Line grand total</label>
                <p class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-right font-mono text-sm">
                    <span class="vq-line-total">0.00</span>
                </p>
            </div>
        </div>

        <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Delivery &amp; warranty</h4>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Lead time (days)</label>
                <input type="text" name="items[{{ $index }}][lead_time]" value="{{ $row['lead_time'] ?? '' }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Warranty</label>
                <input type="text" name="items[{{ $index }}][warranty]" value="{{ $row['warranty'] ?? '' }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div class="sm:col-span-3">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Remarks</label>
                <textarea name="items[{{ $index }}][remarks]" rows="2"
                          class="admin-form-textarea mt-1 w-full">{{ $row['remarks'] ?? '' }}</textarea>
            </div>
        </div>
    </div>
</article>
