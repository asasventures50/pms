@php
    use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;
    use App\Services\Procurement\PurchaseOrders\ProcurementRequestLineUnitLookup;

    $currency = $invoice->displayCurrency();
    $currencySuffix = $currency ? ' ('.$currency.')' : '';
    $feeRows = $invoice->feeRowsForPrint();
    $nextLineNumber = ((int) $invoice->items->max('line_number')) + 1;
    $projectZoneResolver = $projectZoneResolver ?? null;
    $poItemsById = $poItemsById ?? collect();
    $unitsByLineCode = $unitsByLineCode ?? [];
@endphp

<div class="inv-tables-block">
<div class="inv-table-frame">
<table class="inv-items-table">
    <thead>
    <tr>
        <th class="col-num">م</th>
        <th class="col-project">المنطقة</th>
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
            $zone = $projectZoneResolver instanceof InvoiceProjectZoneResolver
                ? $projectZoneResolver->zoneForInvoiceItem($line, $poItemsById)
                : trim((string) ($line->project_zone ?? ''));
            $zone = $zone !== null && $zone !== '' ? $zone : '—';
            $unit = trim((string) ($line->unit ?? ''));
            if ($unit === '') {
                $sourcePoItem = collect($line->source_purchase_order_item_ids ?? [])
                    ->map(fn ($id) => $poItemsById->get((int) $id))
                    ->filter()
                    ->first();
                if ($sourcePoItem) {
                    $unit = trim((string) (ProcurementRequestLineUnitLookup::resolveForPurchaseOrderItem(
                        $sourcePoItem,
                        $unitsByLineCode,
                    ) ?? ''));
                }
            }
        @endphp
        <tr>
            <td class="inv-cell-num">{{ $line->line_number }}</td>
            <td class="inv-cell-project">{{ $zone }}</td>
            <td class="inv-cell-text">{{ $line->description }}</td>
            <td class="inv-cell-num">{{ number_format($line->quantity, 3) }}</td>
            <td class="inv-cell-num">{{ $unit !== '' ? $unit : '—' }}</td>
            <td class="inv-cell-money">{{ number_format($line->quantity > 0 ? $line->line_total / $line->quantity : 0, 2) }}</td>
            <td class="inv-cell-money">{{ number_format($line->line_total, 2) }}</td>
        </tr>
    @endforeach
    @foreach ($feeRows as $fee)
        @php
            $feeZone = InvoiceProjectZoneResolver::splitStoredLabel($fee['project_zone'] ?? '')['zone'];
            $feeZone = $feeZone !== '' ? $feeZone : '—';
        @endphp
        <tr class="inv-fee-row">
            <td class="inv-cell-num">{{ $nextLineNumber++ }}</td>
            <td class="inv-cell-project">{{ $feeZone }}</td>
            <td class="inv-cell-text">{{ $fee['description'] }}</td>
            <td class="inv-cell-num">{{ number_format($fee['quantity'], 3) }}</td>
            <td class="inv-cell-num">{{ ($fee['unit'] ?? '') !== '' ? $fee['unit'] : '—' }}</td>
            <td class="inv-cell-money">{{ number_format($fee['quantity'] > 0 ? $fee['amount'] / $fee['quantity'] : 0, 2) }}</td>
            <td class="inv-cell-money">{{ number_format($fee['amount'], 2) }}</td>
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
