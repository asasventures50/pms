<div class="po-grid-2 po-parties">
    <div class="po-grid-col">
        <div class="po-section-title">Vendor Details:</div>
        <div class="po-form-group">
            <span class="po-form-label">Company name:</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_company_name ?? $purchaseOrder->vendor?->name ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Office Address:</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_address ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Contact persone:</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_contact ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Email:</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_email ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Phone:</span>
            <span class="po-form-line">{{ $purchaseOrder->vendor_phone ?? '' }}</span>
        </div>
    </div>
    <div class="po-grid-col">
        <div class="po-section-title">Delivery Details</div>
        <div class="po-form-group">
            <span class="po-form-label">Delivery location:</span>
            <span class="po-form-line">{{ $purchaseOrder->delivery_location ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Contact person:</span>
            <span class="po-form-line">{{ $purchaseOrder->delivery_contact_name ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Phone:</span>
            <span class="po-form-line">{{ $purchaseOrder->delivery_contact_phone ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Email:</span>
            <span class="po-form-line">{{ $purchaseOrder->delivery_contact_email ?? '' }}</span>
        </div>
    </div>
</div>
