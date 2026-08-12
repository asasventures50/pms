@php
    $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);

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

    $prItemsByLine = $prContext['pr_items_by_line'] ?? [];

    /** @var array<string, array{text: string, items: list<string>}> $scopesByKey */
    $scopesByKey = [];
    foreach ($purchaseOrder->items as $line) {
        $prLine = $prItemsByLine[trim((string) ($line->item ?? ''))] ?? null;
        $scopeOfWork = trim((string) ($prLine?->scope_of_work ?? ''));
        if ($scopeOfWork === '') {
            continue;
        }

        $normalized = preg_replace('/\s+/u', ' ', $scopeOfWork) ?? $scopeOfWork;
        if (! isset($scopesByKey[$normalized])) {
            $scopesByKey[$normalized] = [
                'text' => $scopeOfWork,
                'items' => [],
            ];
        }

        $itemLabel = trim((string) ($line->item ?? ''));
        if ($itemLabel !== '') {
            $scopesByKey[$normalized]['items'][] = $itemLabel;
        }
    }

    if ($scopesByKey === []) {
        $headerScope = trim((string) ($purchaseOrder->procurementRequest?->scope_of_work ?? ''));
        if ($headerScope !== '') {
            $normalized = preg_replace('/\s+/u', ' ', $headerScope) ?? $headerScope;
            $scopesByKey[$normalized] = [
                'text' => $headerScope,
                'items' => [],
            ];
        }
    }

    $uniqueScopes = array_values($scopesByKey);
    $scopeLabel = str_replace("\n", ' ', $printLabels->t('scope_of_work'));
@endphp

<table class="po-items-table">
    <colgroup>
        <col class="col-item">
        <col class="col-desc">
        <col class="col-qty">
        <col class="col-unit">
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
        <th class="col-item">{{ $printLabels->t('item') }}</th>
        <th class="col-desc">{!! nl2br(e($printLabels->t('description'))) !!}</th>
        <th class="col-qty">{{ $printLabels->t('quantity') }}</th>
        <th class="col-unit">{{ $printLabels->t('unit') }}</th>
        <th class="col-price">{!! nl2br(e($printLabels->t('price_per_unit'))) !!}{{ $currencySuffix }}</th>
        <th class="col-total">{{ $printLabels->t('line_total') }}{{ $currencySuffix }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($purchaseOrder->items as $line)
        @php
            $prLine = $prItemsByLine[trim((string) ($line->item ?? ''))] ?? null;
            $unit = trim((string) ($line->unit ?? ''));
            if ($unit === '' && $prLine) {
                $unit = trim((string) ($prLine->unit ?? ''));
            }
        @endphp
        <tr>
            <td class="po-cell-item">{{ $line->item }}</td>
            <td class="po-cell-text" dir="auto">{{ $line->description }}</td>
            <td class="po-cell-num po-cell-qty">{{ number_format($line->quantity, 3) }}</td>
            <td class="po-cell-num">{{ $unit !== '' ? $unit : '—' }}</td>
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
            <td class="po-totals-label">{{ $printLabels->t('subtotal') }}</td>
            <td class="po-totals-value">{{ $purchaseOrder->formatMoneyAmount($linesSubtotal) }}</td>
        </tr>
        <tr>
            <td class="po-totals-label">{{ $printLabels->t('delivery_fee') }}</td>
            <td class="po-totals-value">{{ $purchaseOrder->formatMoneyAmount($deliveryFee) }}</td>
        </tr>
        <tr>
            <td class="po-totals-label">{{ $printLabels->t('discount') }}</td>
            <td class="po-totals-value">{{ $showDiscountMinus ? '−' : '' }}{{ $purchaseOrder->formatMoneyAmount($discount) }}</td>
        </tr>
        <tr>
            <td class="po-totals-label">{{ $printLabels->t('total_price') }}</td>
            <td class="po-totals-value">{{ $purchaseOrder->formatMoneyAmount($totalPrice) }}</td>
        </tr>
    </table>
</div>

@if ($uniqueScopes !== [])
    <div class="po-scope-of-work">
        @foreach ($uniqueScopes as $scopeEntry)
            <div class="po-scope-of-work__entry">
                <div class="po-field-label">
                    {{ $scopeLabel }}@if (count($uniqueScopes) > 1 && $scopeEntry['items'] !== [])
                        — {{ implode(', ', $scopeEntry['items']) }}
                    @endif
                </div>
                <div class="po-field-value" dir="auto">{{ $scopeEntry['text'] }}</div>
            </div>
        @endforeach
    </div>
@endif
