@php
    $deliveryRows = array_filter([
        'Delivery location' => trim((string) ($purchaseOrder->delivery_location ?? '')),
        'Contact person' => trim((string) ($purchaseOrder->delivery_contact_name ?? '')),
        'Phone' => trim((string) ($purchaseOrder->delivery_contact_phone ?? '')),
        'Email' => trim((string) ($purchaseOrder->delivery_contact_email ?? '')),
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
            <div class="po-section-title">Delivery Details</div>
            @foreach ($deliveryRows as $label => $value)
                <div class="po-form-group">
                    <span class="po-form-label">{{ $label }}:</span>
                    <span class="po-form-line">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
