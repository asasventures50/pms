@php
    $index = $index ?? 0;
    $row = $row ?? [];
@endphp

<div class="rfq-line-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <p class="rfq-line-label text-xs font-medium uppercase tracking-wide text-slate-500">Line {{ $index + 1 }}</p>
        <button type="button" class="rfq-remove-line text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
    </div>

    <div class="mt-3 grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-slate-600">Procurement request item</label>
            <select data-name="procurement_request_item_id"
                    class="rfq-pr-item-select admin-filter-control mt-1 w-full" required>
                <option value="">— Select PR item —</option>
                @foreach ($prItemOptions as $opt)
                    <option value="{{ $opt['id'] }}"
                            data-item="{{ $opt['item'] }}"
                            data-description="{{ $opt['description'] }}"
                            data-quantity="{{ $opt['quantity'] }}"
                            data-unit="{{ $opt['unit'] }}"
                            @selected((string) ($row['procurement_request_item_id'] ?? '') === (string) $opt['id'])>
                        {{ $opt['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600">Item code</label>
            <input type="text" data-name="item" value="{{ $row['item'] ?? '' }}" readonly
                   class="rfq-pr-field admin-filter-control mt-1 w-full bg-slate-50">
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600">Lead time</label>
            <input type="text" data-name="request_lead_time" value="{{ $row['request_lead_time'] ?? '' }}"
                   class="admin-filter-control mt-1 w-full" placeholder="e.g. 2 weeks">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-slate-600">Description</label>
            <textarea data-name="description" rows="2" readonly required
                      class="rfq-pr-field admin-filter-control mt-1 w-full resize-none bg-slate-50">{{ $row['description'] ?? '' }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600">Quantity</label>
            <input type="number" data-name="quantity" value="{{ $row['quantity'] ?? 1 }}" min="0" step="0.001" readonly required
                   class="rfq-pr-field admin-filter-control mt-1 w-full bg-slate-50">
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-600">Unit</label>
            <input type="text" data-name="unit" value="{{ $row['unit'] ?? '' }}" readonly
                   class="rfq-pr-field admin-filter-control mt-1 w-full bg-slate-50">
        </div>
    </div>

    @if (! config('procurement.rfq.show_extended_form_fields'))
        <input type="hidden" data-name="compliance" value="{{ $row['compliance'] ?? '' }}">
        <input type="hidden" data-name="unit_price" value="{{ $row['unit_price'] ?? '' }}">
        <input type="hidden" data-name="quote_lead_time" value="{{ $row['quote_lead_time'] ?? '' }}">
        <input type="hidden" data-name="warranty" value="{{ $row['warranty'] ?? '' }}">
    @endif
</div>
