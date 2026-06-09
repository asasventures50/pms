@php
    $formData = $formData ?? [];
    $currency = strtoupper(trim((string) ($procurementRequest->currency_code ?? '')));
    $currencySuffix = $currency !== '' ? ' ('.$currency.')' : '';
@endphp

<div class="po-section-title">BOQ</div>

<table class="po-items-table pr-items-table">
    <colgroup>
        <col style="width:14%">
        <col style="width:40%">
        <col style="width:10%">
        <col style="width:10%">
        <col style="width:13%">
        <col style="width:13%">
    </colgroup>
    <thead>
    <tr>
        <th>Item</th>
        <th>Description</th>
        <th>Qty</th>
        <th>Unit</th>
        <th>Unit price{{ $currencySuffix }}</th>
        <th>Total{{ $currencySuffix }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($procurementRequest->items as $line)
        @php
            $lineNo = filled($line->item_name) ? $line->item_name : ($line->line_number ?: '—');
        @endphp
        <tr>
            <td class="po-cell-item">{{ $lineNo }}</td>
            <td class="po-cell-text pr-cell-wrap">{{ $line->description }}</td>
            <td class="po-cell-num po-cell-qty">{{ number_format($line->quantity, 3) }}</td>
            <td class="po-cell-num">{{ $line->unit ?: '—' }}</td>
            <td class="po-cell-num">{{ number_format((float) $line->unit_price, 4) }}</td>
            <td class="po-cell-num">{{ number_format((float) ($line->total_price ?? 0), 4) }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="pr-empty-table">No line items.</td></tr>
    @endforelse
    </tbody>
</table>

@if ($procurementRequest->samples_required !== null)
    <p class="mt-2 text-sm"><strong>Samples required:</strong> {{ $procurementRequest->samples_required ? 'Yes' : 'No' }}</p>
@endif

@if (trim((string) ($formData['justification'] ?? '')) !== '')
    <p class="mt-3 text-sm"><strong>Justification:</strong> {{ $formData['justification'] }}</p>
@endif

@if (filled($procurementRequest->delivery_lead_time_days) || filled($formData['delivery_location'] ?? null))
    <div class="po-grid-2 mt-3">
        @if (filled($procurementRequest->delivery_lead_time_days))
            <div class="po-form-group">
                <span class="po-form-label">Lead time (days):</span>
                <span class="po-form-line">{{ $procurementRequest->delivery_lead_time_days }}</span>
            </div>
        @endif
        @if (filled($formData['delivery_location'] ?? null))
            <div class="po-form-group">
                <span class="po-form-label">Delivery location:</span>
                <span class="po-form-line">{{ $formData['delivery_location'] }}</span>
            </div>
        @endif
    </div>
@endif

@if (trim((string) ($formData['scope_of_work'] ?? '')) !== '')
    <p class="mt-2 text-sm whitespace-pre-wrap"><strong>Scope of work:</strong> {{ $formData['scope_of_work'] }}</p>
@endif
