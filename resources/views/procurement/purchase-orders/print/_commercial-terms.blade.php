@php
    use App\Services\Procurement\PurchaseOrders\ProcurementRequestCommercialTermsForPurchaseOrder;

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
    <div class="po-section-title">Retention by year</div>
    <table class="po-items-table pr-items-table pr-compact-table">
        <thead>
        <tr><th>Retention %</th><th>Release period</th></tr>
        </thead>
        <tbody>
        @foreach ($retentions as $row)
            <tr>
                <td>{{ $row['retention_percent'] ?? '—' }}</td>
                <td>{{ $row['release_period'] ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if ($showMaintenance)
    <div class="po-section-title">Maintenance (internal)</div>
    <div class="po-grid-2">
        <div class="po-grid-col">
            <div class="po-form-group">
                <span class="po-form-label">After-sale service:</span>
                <span class="po-form-line">
                    @if ($purchaseOrder->after_sale_service_applicable === null)
                        —
                    @elseif ($purchaseOrder->after_sale_service_applicable)
                        Yes
                    @else
                        No
                    @endif
                </span>
            </div>
            <div class="po-form-group">
                <span class="po-form-label">Warranty &amp; guarantee period (years):</span>
                <span class="po-form-line">{{ $purchaseOrder->warranty_years ?? '—' }}</span>
            </div>
            @if (trim((string) ($purchaseOrder->warranty_coverage ?? '')) !== '')
                <div class="po-form-group">
                    <span class="po-form-label">Coverage / scope:</span>
                    <span class="po-form-line">{{ $purchaseOrder->warranty_coverage }}</span>
                </div>
            @endif
        </div>
    </div>
@endif
