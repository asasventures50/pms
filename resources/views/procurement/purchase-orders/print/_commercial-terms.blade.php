@php
    use App\Services\Procurement\PurchaseOrders\ProcurementRequestCommercialTermsForPurchaseOrder;

    $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);

    $retentions = is_array($purchaseOrder->retentions) ? $purchaseOrder->retentions : [];
    $hasRetention = app(ProcurementRequestCommercialTermsForPurchaseOrder::class)->hasRetentionRows($retentions);
    $showRetention = $purchaseOrder->show_retention && $hasRetention;
    $showMaintenance = $purchaseOrder->show_maintenance && (
        $purchaseOrder->after_sale_service_applicable !== null
        || $purchaseOrder->warranty_years !== null
        || trim((string) ($purchaseOrder->warranty_coverage ?? '')) !== ''
    );
@endphp

@if ($showRetention)
    <div class="po-section-title">{{ $printLabels->t('retention_by_year') }}</div>
    <table class="po-items-table pr-items-table pr-compact-table">
        <thead>
        <tr>
            <th>{{ $printLabels->t('retention_percent') }}</th>
            <th>{{ $printLabels->t('release_period') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($retentions as $row)
            <tr>
                <td>{{ $row['retention_percent'] ?? $printLabels->t('em_dash') }}</td>
                <td>{{ $row['release_period'] ?? $printLabels->t('em_dash') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if ($showMaintenance)
    <div class="po-section-title">{{ $printLabels->t('maintenance_internal') }}</div>
    <div class="po-grid-2">
        <div class="po-grid-col">
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('after_sale_service') }}</span>
                <span class="po-form-line">{{ $printLabels->yesNo($purchaseOrder->after_sale_service_applicable) }}</span>
            </div>
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('warranty_years') }}</span>
                <span class="po-form-line">{{ $purchaseOrder->warranty_years ?? $printLabels->t('em_dash') }}</span>
            </div>
            @if (trim((string) ($purchaseOrder->warranty_coverage ?? '')) !== '')
                <div class="po-form-group">
                    <span class="po-form-label">{{ $printLabels->t('coverage_scope') }}</span>
                    <span class="po-form-line">{{ $purchaseOrder->warranty_coverage }}</span>
                </div>
            @endif
        </div>
    </div>
@endif
