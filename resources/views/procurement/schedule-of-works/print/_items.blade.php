@php
    $currency = $schedule->displayCurrency();
    $currencySuffix = $currency ? ' ('.$currency.')' : '';
@endphp

<div class="inv-tables-block">
<div class="inv-table-frame">
<table class="inv-items-table">
    <thead>
    <tr>
        <th class="col-num">{{ $printLabels->t('col_num') }}</th>
        <th class="col-project">{{ $printLabels->t('col_project') }}</th>
        <th class="col-desc">{{ $printLabels->t('col_desc') }}</th>
        <th class="col-qty">{{ $printLabels->t('col_qty') }}</th>
        <th class="col-unit">{{ $printLabels->t('col_unit') }}</th>
        <th class="col-price">{{ $printLabels->t('col_unit_price') }}{{ $currencySuffix }}</th>
        <th class="col-total">{{ $printLabels->t('col_total') }}{{ $currencySuffix }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($schedule->items as $line)
        @php
            $projectZone = trim((string) ($line->project_zone ?? ''));
            $unit = trim((string) ($line->unit ?? ''));
        @endphp
        <tr>
            <td class="inv-cell-num">{{ $line->line_number }}</td>
            <td class="inv-cell-project">{{ $projectZone !== '' ? $projectZone : $printLabels->t('em_dash') }}</td>
            <td class="inv-cell-text">{{ $line->description }}</td>
            <td class="inv-cell-num">{{ number_format($line->quantity, 3) }}</td>
            <td class="inv-cell-num">{{ $unit !== '' ? $unit : $printLabels->t('em_dash') }}</td>
            <td class="inv-cell-money">{{ number_format($line->unit_price, 2) }}</td>
            <td class="inv-cell-money">{{ number_format($line->line_total, 2) }}</td>
        </tr>
    @endforeach
    <tr class="inv-totals-grand">
        <td colspan="6" class="inv-fee-label">{{ $printLabels->t('grand_total') }}</td>
        <td class="inv-cell-money inv-grand-total-amount">{{ $schedule->formatMoneyAmount($schedule->total_price) }}</td>
    </tr>
    </tbody>
</table>
</div>
</div>
