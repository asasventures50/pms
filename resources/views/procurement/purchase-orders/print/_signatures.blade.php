@php
    $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);
@endphp

<div class="po-signatures">
    <div class="po-signature-col">
        <div class="po-signature-row">
            <span class="po-form-label">{{ $printLabels->t('procurement') }}</span>
            <span class="po-form-line">{{ $purchaseOrder->procurement_signature ?? '' }}</span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">{{ $printLabels->t('signature') }}</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">{{ $printLabels->t('date') }}</span>
            <span class="po-form-line">{{ $purchaseOrder->procurement_signed_at?->format('d-m-Y') ?? '' }}</span>
        </div>
    </div>
    <div class="po-signature-col">
        <div class="po-signature-row">
            <span class="po-form-label">{{ $printLabels->t('vendor') }}</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_signature ?? '' }}</span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">{{ $printLabels->t('signature') }}</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">{{ $printLabels->t('date') }}</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_signed_at?->format('d-m-Y') ?? '' }}</span>
        </div>
    </div>
</div>
