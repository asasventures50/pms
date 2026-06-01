<div class="po-signatures">
    <div class="po-signature-col">
        <div class="po-signature-row">
            <span class="po-form-label">Procurement:</span>
            <span class="po-form-line">{{ $purchaseOrder->procurement_signature ?? '' }}</span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Signature:</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Date:</span>
            <span class="po-form-line">{{ $purchaseOrder->procurement_signed_at?->format('d-m-Y') ?? '' }}</span>
        </div>
    </div>
    <div class="po-signature-col">
        <div class="po-signature-row">
            <span class="po-form-label">Vendor:</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_signature ?? '' }}</span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Signature:</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Date:</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_signed_at?->format('d-m-Y') ?? '' }}</span>
        </div>
    </div>
</div>
