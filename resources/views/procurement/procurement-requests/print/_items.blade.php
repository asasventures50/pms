@php
    $printLabels = $printLabels ?? \App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels::resolve(null);
    $formData = $formData ?? [];
    $currency = strtoupper(trim((string) ($procurementRequest->currency_code ?? '')));
    $currencySuffix = $currency !== '' ? ' ('.$currency.')' : '';
    $emDash = $printLabels->t('em_dash');
@endphp

<div class="po-section-title">{{ $printLabels->t('boq') }}</div>

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
        <th>{{ $printLabels->t('item') }}</th>
        <th>{{ $printLabels->t('description') }}</th>
        <th>{{ $printLabels->t('qty') }}</th>
        <th>{{ $printLabels->t('unit') }}</th>
        <th>{{ $printLabels->t('unit_price') }}{{ $currencySuffix }}</th>
        <th>{{ $printLabels->t('total') }}{{ $currencySuffix }}</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($procurementRequest->items as $line)
        @php
            $lineNo = filled($line->item_name) ? $line->item_name : ($line->line_number ?: $emDash);
        @endphp
        <tr>
            <td class="po-cell-item">{{ $lineNo }}</td>
            <td class="po-cell-text pr-cell-wrap">{{ $line->description }}</td>
            <td class="po-cell-num po-cell-qty">{{ number_format($line->quantity, 3) }}</td>
            <td class="po-cell-num">{{ $line->unit ?: $emDash }}</td>
            <td class="po-cell-num">{{ number_format((float) $line->unit_price, 4) }}</td>
            <td class="po-cell-num">{{ number_format((float) ($line->total_price ?? 0), 4) }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="pr-empty-table">{{ $printLabels->t('no_line_items') }}</td></tr>
    @endforelse
    </tbody>
</table>

@if ($procurementRequest->samples_required !== null)
    <p class="mt-2 text-sm"><strong>{{ $printLabels->t('samples_required') }}</strong> {{ $printLabels->yesNo($procurementRequest->samples_required) }}</p>
@endif

@if (trim((string) ($formData['justification'] ?? '')) !== '')
    <p class="mt-3 text-sm"><strong>{{ $printLabels->t('justification') }}</strong> {{ $formData['justification'] }}</p>
@endif

@if (filled($procurementRequest->delivery_lead_time_days) || filled($formData['delivery_location'] ?? null))
    <div class="po-grid-2 mt-3">
        @if (filled($procurementRequest->delivery_lead_time_days))
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('lead_time_days') }}</span>
                <span class="po-form-line">{{ $procurementRequest->delivery_lead_time_days }}</span>
            </div>
        @endif
        @if (filled($formData['delivery_location'] ?? null))
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('delivery_location') }}</span>
                <span class="po-form-line">{{ $formData['delivery_location'] }}</span>
            </div>
        @endif
    </div>
@endif

@if (trim((string) ($formData['scope_of_work'] ?? '')) !== '')
    <p class="mt-2 text-sm whitespace-pre-wrap"><strong>{{ $printLabels->t('scope_of_work') }}</strong> {{ $formData['scope_of_work'] }}</p>
@endif
