@php
    $index = $index ?? 0;
    $row = $row ?? [];
    $selectedId = (string) ($row['procurement_request_item_id'] ?? '');
    $selectedOpt = collect($prItemOptions)->firstWhere('id', (int) $selectedId ?: null);
@endphp

<div class="rfq-line-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
        <p class="rfq-line-label text-sm font-semibold text-slate-900">Line {{ $index + 1 }}</p>
        <button type="button" class="rfq-remove-line rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50">Remove</button>
    </div>

    <div class="mt-4">
        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Procurement request item</label>
        <select data-name="procurement_request_item_id"
                class="rfq-pr-item-select admin-filter-control mt-1 w-full" required>
            <option value="">— Select PR item —</option>
            @foreach ($prItemOptions as $opt)
                <option value="{{ $opt['id'] }}" @selected($selectedId === (string) $opt['id'])>
                    {{ $opt['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="rfq-pr-details mt-4 space-y-4{{ $selectedOpt ? '' : ' hidden' }}">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">PR number</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="pr_number">{{ $selectedOpt['pr_number'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Line number</dt>
                <dd class="rfq-display-field mt-1 font-mono text-sm text-slate-900" data-display="line_item">{{ $selectedOpt['item'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Project</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="project">{{ $selectedOpt['project'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Zone</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="zone">{{ $selectedOpt['zone'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="category">{{ $selectedOpt['category'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Sub category</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="subcategory">{{ $selectedOpt['subcategory'] ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Scope type</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="scope_type">{{ $selectedOpt['scope_type'] ?? '—' }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Item description</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="description">{{ $selectedOpt['description'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Unit</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="unit">{{ $selectedOpt['unit'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Quantity</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="quantity">{{ isset($selectedOpt['quantity']) ? number_format($selectedOpt['quantity'], 3) : '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Justification</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="justification">{{ $selectedOpt['justification'] ?? '—' }}</dd>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-4">
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Scope of work</dt>
            <dd class="rfq-display-field mt-1 max-w-full break-words whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-900"
                data-display="scope_of_work">{{ $selectedOpt['scope_of_work'] ?? '—' }}</dd>
        </div>

        <div class="grid gap-4 border-t border-slate-100 pt-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Required delivery date</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="required_delivery_date">{{ $selectedOpt['required_delivery_date'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Flexible delivery date</dt>
                <dd class="rfq-display-field mt-1 text-sm text-slate-900" data-display="flexible_delivery_date">
                    @if ($selectedOpt)
                        {{ ($selectedOpt['flexible_delivery_date'] ?? false) ? 'Yes' : 'No' }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Delivery location</dt>
                <dd class="rfq-display-field mt-1 break-words text-sm text-slate-900" data-display="delivery_location">{{ $selectedOpt['delivery_location'] ?? '—' }}</dd>
            </div>
        </div>
    </div>

    <input type="hidden" data-name="item" value="{{ $row['item'] ?? ($selectedOpt['item'] ?? '') }}">
    <input type="hidden" data-name="description" value="{{ $row['description'] ?? ($selectedOpt['description'] ?? '') }}">
    <input type="hidden" data-name="quantity" value="{{ $row['quantity'] ?? ($selectedOpt['quantity'] ?? 1) }}">
    <input type="hidden" data-name="unit" value="{{ $row['unit'] ?? ($selectedOpt['unit'] ?? '') }}">
    <input type="hidden" data-name="request_lead_time" value="{{ $row['request_lead_time'] ?? ($selectedOpt['request_lead_time'] ?? '') }}">

    @if (! config('procurement.rfq.show_extended_form_fields'))
        <input type="hidden" data-name="compliance" value="{{ $row['compliance'] ?? '' }}">
        <input type="hidden" data-name="unit_price" value="{{ $row['unit_price'] ?? '' }}">
        <input type="hidden" data-name="quote_lead_time" value="{{ $row['quote_lead_time'] ?? '' }}">
        <input type="hidden" data-name="warranty" value="{{ $row['warranty'] ?? '' }}">
    @endif
</div>
