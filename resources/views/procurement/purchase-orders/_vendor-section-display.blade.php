@php
    use App\Models\Procurement\PurchaseOrders\PurchaseOrder;

    $po = $purchaseOrder ?? $po ?? null;

    $vendorRows = $po instanceof PurchaseOrder
        ? $po->vendorDisplayRows()
        : [];
@endphp

@if ($variant === 'print')
    @php
        $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);
    @endphp
    @if ($vendorRows !== [])
        <div class="po-vendor-details">
            <div class="po-section-title">{{ $printLabels->t('vendor_details') }}</div>
            @foreach ($vendorRows as $label => $value)
                <div class="po-form-group">
                    <span class="po-form-label">{{ $printLabels->vendorRowLabel($label) }}:</span>
                    <span class="po-form-line">
                        @if (in_array($label, ['Phone', 'WhatsApp', 'Email'], true))
                            <span class="po-ltr">{{ $value }}</span>
                        @else
                            {{ $value }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif
@else
    @if ($vendorRows === [])
        <p class="text-sm text-slate-500">No vendor details recorded.</p>
    @else
        <dl class="grid gap-3 sm:grid-cols-2 text-sm">
            @foreach ($vendorRows as $label => $value)
                <div @class(['sm:col-span-2' => $label === 'Classification'])>
                    <dt class="text-xs text-slate-500">{{ $label }}</dt>
                    <dd class="mt-0.5 text-slate-900 @if ($label === 'Classification') whitespace-pre-wrap @endif">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
@endif
