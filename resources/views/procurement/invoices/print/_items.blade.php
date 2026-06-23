@php
    use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;

    $currency = $invoice->displayCurrency();
    $currencySuffix = $currency ? ' ('.$currency.')' : '';
    $feeRows = $invoice->feeRowsForPrint();
    $nextLineNumber = ((int) $invoice->items->max('line_number')) + 1;
    $projectZoneResolver = $projectZoneResolver ?? null;
    $poItemsById = $poItemsById ?? collect();
@endphp

<div class="inv-tables-block">
<div class="inv-table-frame">
<table class="inv-items-table">
    <thead>
    <tr>
        <th class="col-num">م</th>
        <th class="col-project">المشروع / المنطقة</th>
        <th class="col-desc">البيان</th>
        <th class="col-qty">الكمية</th>
        <th class="col-unit">الوحدة</th>
        <th class="col-price">سعر الوحدة{{ $currencySuffix }}</th>
        <th class="col-total">المجموع{{ $currencySuffix }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($invoice->items as $line)
        @php
            $projectZone = $projectZoneResolver instanceof InvoiceProjectZoneResolver
                ? $projectZoneResolver->forInvoiceItem($line, $poItemsById)
                : trim((string) ($line->project_zone ?? ''));
            $projectZone = $projectZone !== '' ? $projectZone : '—';
        @endphp
        <tr>
            <td class="inv-cell-num">{{ $line->line_number }}</td>
            <td class="inv-cell-project">{{ $projectZone }}</td>
            <td class="inv-cell-text">{{ $line->description }}</td>
            <td class="inv-cell-num">{{ number_format($line->quantity, 3) }}</td>
            <td class="inv-cell-num">{{ $line->unit ?: '—' }}</td>
            <td class="inv-cell-money">{{ number_format($line->unit_price, 2) }}</td>
            <td class="inv-cell-money">{{ number_format($line->line_total, 2) }}</td>
        </tr>
    @endforeach
    @foreach ($feeRows as $fee)
        <tr class="inv-fee-row">
            <td class="inv-cell-num">{{ $nextLineNumber++ }}</td>
            <td colspan="5" class="inv-fee-label">{{ $fee['label'] }}</td>
            <td class="inv-cell-money inv-fee-value">{{ $invoice->formatMoneyAmount($fee['amount']) }}</td>
        </tr>
    @endforeach
    <tr class="inv-totals-grand">
        <td colspan="6" class="inv-fee-label">المجموع الكلي</td>
        <td class="inv-cell-money inv-grand-total-amount">{{ $invoice->formatMoneyAmount($invoice->total_price) }}</td>
    </tr>
    </tbody>
</table>
</div>
</div>
