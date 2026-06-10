@php
    use App\Services\Procurement\PurchaseOrders\ProcurementRequestCommercialTermsForPurchaseOrder;

    $purchaseOrder = $purchaseOrder ?? null;
    $retentions = is_array($purchaseOrder?->retentions) ? $purchaseOrder->retentions : [];
    $hasRetention = app(ProcurementRequestCommercialTermsForPurchaseOrder::class)->hasRetentionRows($retentions);
    $showRetention = $purchaseOrder->show_retention && $hasRetention;
    $showMaintenance = $purchaseOrder->show_maintenance && (
        $purchaseOrder->after_sale_service_applicable !== null
        || $purchaseOrder->warranty_years !== null
        || trim((string) ($purchaseOrder->warranty_coverage ?? '')) !== ''
    );
@endphp

@if ($purchaseOrder && $showRetention)
    <div>
        <dt class="text-xs text-slate-500">Retention by year</dt>
        <dd class="mt-1 text-slate-900">
            <table class="min-w-full text-left text-sm">
                <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="py-1 pr-4">Retention %</th>
                    <th class="py-1">Release period</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($retentions as $row)
                    <tr>
                        <td class="py-1 pr-4">{{ $row['retention_percent'] ?? '—' }}</td>
                        <td class="py-1">{{ $row['release_period'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </dd>
    </div>
@endif

@if ($purchaseOrder && $showMaintenance)
    <div>
        <dt class="text-xs text-slate-500">Maintenance (internal)</dt>
        <dd class="mt-1 space-y-2 text-slate-900">
            <p>
                <span class="text-xs text-slate-500">After-sale service:</span>
                @if ($purchaseOrder->after_sale_service_applicable === null)
                    —
                @elseif ($purchaseOrder->after_sale_service_applicable)
                    Yes
                @else
                    No
                @endif
            </p>
            <p>
                <span class="text-xs text-slate-500">Warranty &amp; guarantee period (years):</span>
                {{ $purchaseOrder->warranty_years ?? '—' }}
            </p>
            @if (trim((string) ($purchaseOrder->warranty_coverage ?? '')) !== '')
                <p>
                    <span class="text-xs text-slate-500">Coverage / scope:</span>
                    {{ $purchaseOrder->warranty_coverage }}
                </p>
            @endif
        </dd>
    </div>
@endif
