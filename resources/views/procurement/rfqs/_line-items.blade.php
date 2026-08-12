@php
    $lineItems = $lineItems ?? [];
    $prItemOptions = $prItemOptions ?? [];
    if ($lineItems === []) {
        $lineItems = [[
            'procurement_request_item_id' => '',
            'item' => '',
            'description' => '',
            'quantity' => 1,
            'unit' => '',
            'request_lead_time' => '',
            'compliance' => '',
            'unit_price' => '',
            'quote_lead_time' => '',
            'warranty' => '',
        ]];
    }
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Request lines</h3>
            <p class="mt-1 text-xs text-slate-500">Select a PR item per line, or pick many at once. Item details and delivery date come from the procurement request.</p>
        </div>
        @if ($prItemOptions !== [])
            <button type="button" id="rfq-open-bulk-picker"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Select multiple PR items
            </button>
        @endif
    </div>

    @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

    <div id="rfq-lines-body" class="mt-4 space-y-4">
        @foreach ($lineItems as $index => $row)
            @include('procurement.rfqs._line-item-card', [
                'index' => $index,
                'row' => $row,
                'prItemOptions' => $prItemOptions,
            ])
        @endforeach
    </div>

    @include('procurement.partials._add-line-button', ['id' => 'rfq-add-line'])

    @if ($prItemOptions === [])
        <p class="mt-4 text-sm text-amber-700">
            No procurement request items are available. Add PR line items first, or they may already be on another RFQ.
        </p>
    @endif

    <template id="rfq-line-template">
        @include('procurement.rfqs._line-item-card', [
            'index' => 0,
            'row' => [
                'procurement_request_item_id' => '',
                'item' => '',
                'description' => '',
                'quantity' => 1,
                'unit' => '',
                'request_lead_time' => '',
                'compliance' => '',
                'unit_price' => '',
                'quote_lead_time' => '',
                'warranty' => '',
            ],
            'prItemOptions' => $prItemOptions,
        ])
    </template>
</section>

@if ($prItemOptions !== [])
<div id="rfq-bulk-picker-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="rfq-bulk-picker-title">
    <div class="absolute inset-0 bg-slate-900/50" data-rfq-bulk-picker-dismiss></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 id="rfq-bulk-picker-title" class="text-lg font-semibold text-slate-900">Select PR items</h3>
                <p class="mt-1 text-sm text-slate-500">Check the lines you need, then add them all at once.</p>
                <div class="mt-3">
                    <label for="rfq-bulk-picker-filter" class="sr-only">Filter PR items</label>
                    <input type="search" id="rfq-bulk-picker-filter" placeholder="Filter by PR number, description, project…"
                           class="admin-filter-control w-full">
                </div>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-3">
                <p id="rfq-bulk-picker-empty" class="hidden py-6 text-center text-sm text-slate-500">No available PR items left to add.</p>
                <table id="rfq-bulk-picker-table" class="min-w-full text-left text-sm">
                    <thead class="sticky top-0 bg-white text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="w-10 py-2 pr-2">
                            <input type="checkbox" id="rfq-bulk-picker-select-all" class="rounded border-slate-300" title="Select all visible">
                        </th>
                        <th class="py-2 pr-3">PR</th>
                        <th class="py-2 pr-3">Line</th>
                        <th class="py-2 pr-3">Project</th>
                        <th class="py-2">Description</th>
                    </tr>
                    </thead>
                    <tbody id="rfq-bulk-picker-body" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                <p id="rfq-bulk-picker-count" class="text-sm text-slate-600">0 selected</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-rfq-bulk-picker-dismiss
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="button" id="rfq-bulk-picker-confirm"
                            class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover disabled:cursor-not-allowed disabled:opacity-50"
                            disabled>
                        Add selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script type="application/json" id="rfq-pr-item-options">@json($prItemOptions)</script>
