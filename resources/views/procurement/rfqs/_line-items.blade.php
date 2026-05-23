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

<section class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-6">
    <div>
        <h3 class="text-sm font-semibold text-slate-900">Request details</h3>
        <p class="mt-1 text-xs text-slate-500">Select a PR item per line. All item details and the required delivery date come from the procurement request.</p>
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

<script type="application/json" id="rfq-pr-item-options">@json($prItemOptions)</script>
