@php
    $currency = $purchaseOrder->displayCurrency();
    $currencySuffix = $currency ? ' ('.$currency.')' : '';
    $itemCount = $purchaseOrder->items->count();
    $emptyRowCount = max(0, $minItemRows - $itemCount);
    $showDiscountMinus = (float) ($purchaseOrder->discount ?? 0) > 0;
    $linesSubtotal = round((float) $purchaseOrder->items->sum('line_total'), 2);
    $deliveryFee = round((float) ($purchaseOrder->delivery_fee ?? 0), 2);
    $discount = round((float) ($purchaseOrder->discount ?? 0), 2);
    $totalPrice = round(max(0, $linesSubtotal + $deliveryFee - $discount), 2);
    $vendorLabel = trim((string) ($purchaseOrder->vendor_company_name ?? $purchaseOrder->vendor?->name ?? ''));
@endphp

<table class="po-items-table">
    <colgroup>
        <col class="col-item">
        <col class="col-desc">
        <col class="col-scope">
        <col class="col-qty">
        <col class="col-price">
        <col class="col-total">
    </colgroup>
    <thead>
    <tr class="po-thead-meta">
        <th colspan="6">
            P.O. {{ $purchaseOrder->po_number }}
            @if ($purchaseOrder->ordered_at)
                · {{ $purchaseOrder->ordered_at->format('d-m-Y') }}
            @endif
            @if ($vendorLabel !== '')
                · {{ $vendorLabel }}
            @endif
        </th>
    </tr>
    <tr>
        <th class="col-item">Item</th>
        <th class="col-desc">Item or service<br>description</th>
        <th class="col-scope">Scope of<br>work</th>
        <th class="col-qty">Quantity</th>
        <th class="col-price">Price per<br>unit{{ $currencySuffix }}</th>
        <th class="col-total">Line Total{{ $currencySuffix }}</th>
    </tr>
    </thead>
    <tbody>
    @php
        $prItemsByLine = $prContext['pr_items_by_line'] ?? [];
    @endphp
    @foreach ($purchaseOrder->items as $line)
        @php
            $prLine = $prItemsByLine[trim((string) ($line->item ?? ''))] ?? null;
            $scopeOfWork = trim((string) ($prLine?->scope_of_work ?? ''));
        @endphp
        <tr>
            <td class="po-cell-item">{{ $line->item }}</td>
            <td class="po-cell-text">{{ $line->description }}</td>
            <td class="po-cell-text">{{ $scopeOfWork }}</td>
            <td class="po-cell-num po-cell-qty">{{ number_format($line->quantity, 3) }}</td>
            <td class="po-cell-num po-cell-money">{{ number_format($line->unit_price, 2) }}</td>
            <td class="po-cell-num po-cell-money">{{ number_format($line->line_total, 2) }}</td>
        </tr>
    @endforeach
    @for ($i = 0; $i < $emptyRowCount; $i++)
        <tr>
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endfor
    @if ($itemCount === 0 && $emptyRowCount === 0)
        @for ($i = 0; $i < $minItemRows; $i++)
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endfor
    @endif
    </tbody>
</table>

<div class="po-totals-wrap">
    <table class="po-totals-table">
        <tr>
            <td class="po-totals-label">Subtotal</td>
            <td class="po-totals-value">{{ $purchaseOrder->formatMoneyAmount($linesSubtotal) }}</td>
        </tr>
        <tr>
            <td class="po-totals-label">Delivery fee</td>
            <td class="po-totals-value">{{ $purchaseOrder->formatMoneyAmount($deliveryFee) }}</td>
        </tr>
        <tr>
            <td class="po-totals-label">Discount</td>
            <td class="po-totals-value">{{ $showDiscountMinus ? '−' : '' }}{{ $purchaseOrder->formatMoneyAmount($discount) }}</td>
        </tr>
        <tr>
            <td class="po-totals-label">Total Price:</td>
            <td class="po-totals-value">{{ $purchaseOrder->formatMoneyAmount($totalPrice) }}</td>
        </tr>
    </table>
</div>
