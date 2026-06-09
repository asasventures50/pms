@php
    use App\Services\Procurement\PurchaseOrders\ProcurementRequestCommercialTermsForPurchaseOrder;

    $retentions = is_array($purchaseOrder->retentions) ? $purchaseOrder->retentions : [];
    $hasRetention = app(ProcurementRequestCommercialTermsForPurchaseOrder::class)->hasRetentionRows($retentions);
    $showRetention = $purchaseOrder->show_retention && $hasRetention;
    $showInsurance = $purchaseOrder->show_insurance && (
        $purchaseOrder->primary_insurance_applicable !== null
        || $purchaseOrder->final_insurance_applicable !== null
        || trim((string) ($purchaseOrder->primary_insurance_requirements ?? '')) !== ''
        || trim((string) ($purchaseOrder->final_insurance_requirements ?? '')) !== ''
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

@if ($showInsurance)
    <div class="po-section-title">Insurance requirements</div>
    <div class="po-grid-2">
        <div class="po-grid-col">
            <div class="po-form-group">
                <span class="po-form-label">Primary insurance:</span>
                <span class="po-form-line">
                    @if ($purchaseOrder->primary_insurance_applicable === null)
                        —
                    @elseif ($purchaseOrder->primary_insurance_applicable)
                        Yes
                    @else
                        No
                    @endif
                </span>
            </div>
            @if (trim((string) ($purchaseOrder->primary_insurance_requirements ?? '')) !== '')
                <div class="po-form-group">
                    <span class="po-form-label">Primary requirements:</span>
                    <span class="po-form-line">{{ $purchaseOrder->primary_insurance_requirements }}</span>
                </div>
            @endif
            <div class="po-form-group">
                <span class="po-form-label">Final insurance:</span>
                <span class="po-form-line">
                    @if ($purchaseOrder->final_insurance_applicable === null)
                        —
                    @elseif ($purchaseOrder->final_insurance_applicable)
                        Yes
                    @else
                        No
                    @endif
                </span>
            </div>
            @if (trim((string) ($purchaseOrder->final_insurance_requirements ?? '')) !== '')
                <div class="po-form-group">
                    <span class="po-form-label">Final requirements:</span>
                    <span class="po-form-line">{{ $purchaseOrder->final_insurance_requirements }}</span>
                </div>
            @endif
        </div>
    </div>
@endif
