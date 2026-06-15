@php
    $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);

    $deliveryRows = array_filter([
        'delivery_location' => trim((string) ($purchaseOrder->delivery_location ?? '')),
        'contact_person' => trim((string) ($purchaseOrder->delivery_contact_name ?? '')),
        'phone' => trim((string) ($purchaseOrder->delivery_contact_phone ?? '')),
        'email' => trim((string) ($purchaseOrder->delivery_contact_email ?? '')),
    ], static fn (string $value) => $value !== '');
@endphp

<div class="po-parties-layout">
    <div class="po-parties-vendor">
        @include('procurement.purchase-orders._vendor-section-display', [
            'purchaseOrder' => $purchaseOrder,
            'variant' => 'print',
        ])
    </div>
    @if ($deliveryRows !== [])
        <div class="po-parties-delivery">
            <div class="po-section-title">{{ $printLabels->t('delivery_details') }}</div>
            @foreach ($deliveryRows as $key => $value)
                <div class="po-form-group">
                    <span class="po-form-label">{{ $printLabels->t($key) }}:</span>
                    <span class="po-form-line">
                        @if (in_array($key, ['phone', 'email'], true))
                            <span class="po-ltr">{{ $value }}</span>
                        @else
                            {{ $value }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
