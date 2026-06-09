@php
    $printLabels = $printLabels ?? \App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels::resolve(null);
    $timeline = $formData['timeline'] ?? [];
    $emDash = $printLabels->t('em_dash');
    $finalDeliveryDays = $procurementRequest->delivery_lead_time_days;
@endphp

<div class="po-section-title">{{ $printLabels->t('procurement_timeline') }}</div>

<table class="po-items-table pr-items-table pr-timeline-table">
    <colgroup>
        <col style="width:70%">
        <col style="width:30%">
    </colgroup>
    <thead>
    <tr>
        <th>{{ $printLabels->t('timeline_activity') }}</th>
        <th>{{ $printLabels->t('timeline_duration_days') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($timeline as $row)
        <tr>
            <td class="po-cell-text">{{ $printLabels->timelineActivityLabel($row['activity'] ?? '') }}</td>
            <td class="po-cell-num">{{ filled($row['duration_days'] ?? null) ? $row['duration_days'] : $emDash }}</td>
        </tr>
    @endforeach
    <tr class="pr-timeline-final-row">
        <td class="po-cell-text pr-timeline-final-label">{{ $printLabels->t('timeline_final_delivery_date') }}</td>
        <td class="po-cell-num">
            {{ filled($finalDeliveryDays) ? $finalDeliveryDays : $emDash }}
            <span class="pr-timeline-days-suffix">{{ $printLabels->t('timeline_days') }}</span>
            <div class="pr-timeline-final-note">{{ $printLabels->t('timeline_final_delivery_note') }}</div>
        </td>
    </tr>
    </tbody>
</table>
