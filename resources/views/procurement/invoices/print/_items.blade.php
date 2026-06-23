@php
    $currency = $invoice->displayCurrency();
    $currencySuffix = $currency ? ' ('.$currency.')' : '';
    $feeRows = [
        ['label' => 'أجور نقل و مواصلات', 'amount' => (float) $invoice->transport_fees],
        ['label' => 'أجور متابعة و اشراف', 'amount' => (float) $invoice->supervision_fees],
        ['label' => 'مصاريف و اجور ادارية', 'amount' => (float) $invoice->administrative_fees],
        ['label' => 'مصاريف و اجور لوجستية', 'amount' => (float) $invoice->logistics_fees],
    ];
@endphp

<div class="inv-tables-block">
<table class="inv-items-table">
    <thead>
    <tr>
        <th class="col-num">م</th>
        <th class="col-desc">البيان</th>
        <th class="col-qty">الكمية</th>
        <th class="col-unit">الوحدة</th>
        <th class="col-price">سعر الوحدة{{ $currencySuffix }}</th>
        <th class="col-total">المجموع{{ $currencySuffix }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($invoice->items as $line)
        <tr>
            <td class="inv-cell-num">{{ $line->line_number }}</td>
            <td class="inv-cell-text">{{ $line->description }}</td>
            <td class="inv-cell-num">{{ number_format($line->quantity, 3) }}</td>
            <td class="inv-cell-num">{{ $line->unit ?: '—' }}</td>
            <td class="inv-cell-money">{{ number_format($line->unit_price, 2) }}</td>
            <td class="inv-cell-money">{{ number_format($line->line_total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="inv-totals-wrap">
    <table class="inv-totals-table">
        @foreach ($feeRows as $fee)
            <tr>
                <td class="inv-totals-label">{{ $fee['label'] }}</td>
                <td class="inv-totals-value">{{ $invoice->formatMoneyAmount($fee['amount']) }}</td>
            </tr>
        @endforeach
        <tr class="inv-totals-grand">
            <td class="inv-totals-label">المجموع الكلي</td>
            <td class="inv-totals-value">{{ $invoice->formatMoneyAmount($invoice->total_price) }}</td>
        </tr>
    </table>
</div>
</div>
