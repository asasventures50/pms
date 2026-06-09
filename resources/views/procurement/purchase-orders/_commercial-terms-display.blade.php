@php
    use App\Services\Procurement\PurchaseOrders\ProcurementRequestCommercialTermsForPurchaseOrder;

    $purchaseOrder = $purchaseOrder ?? null;
    $retentions = is_array($purchaseOrder?->retentions) ? $purchaseOrder->retentions : [];
    $hasRetention = app(ProcurementRequestCommercialTermsForPurchaseOrder::class)->hasRetentionRows($retentions);
    $showRetention = $purchaseOrder->show_retention && $hasRetention;
    $showInsurance = $purchaseOrder->show_insurance && (
        $purchaseOrder->primary_insurance_applicable !== null
        || $purchaseOrder->final_insurance_applicable !== null
        || trim((string) ($purchaseOrder->primary_insurance_requirements ?? '')) !== ''
        || trim((string) ($purchaseOrder->final_insurance_requirements ?? '')) !== ''
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

@if ($purchaseOrder && $showInsurance)
    <div>
        <dt class="text-xs text-slate-500">Insurance requirements</dt>
        <dd class="mt-1 space-y-2 text-slate-900">
            <p>
                <span class="text-xs text-slate-500">Primary insurance:</span>
                @if ($purchaseOrder->primary_insurance_applicable === null)
                    —
                @elseif ($purchaseOrder->primary_insurance_applicable)
                    Yes
                @else
                    No
                @endif
            </p>
            @if (trim((string) ($purchaseOrder->primary_insurance_requirements ?? '')) !== '')
                <p class="whitespace-pre-wrap">
                    <span class="text-xs text-slate-500">Primary requirements:</span>
                    {{ $purchaseOrder->primary_insurance_requirements }}
                </p>
            @endif
            <p>
                <span class="text-xs text-slate-500">Final insurance:</span>
                @if ($purchaseOrder->final_insurance_applicable === null)
                    —
                @elseif ($purchaseOrder->final_insurance_applicable)
                    Yes
                @else
                    No
                @endif
            </p>
            @if (trim((string) ($purchaseOrder->final_insurance_requirements ?? '')) !== '')
                <p class="whitespace-pre-wrap">
                    <span class="text-xs text-slate-500">Final requirements:</span>
                    {{ $purchaseOrder->final_insurance_requirements }}
                </p>
            @endif
        </dd>
    </div>
@endif
