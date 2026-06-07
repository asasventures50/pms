@php
    $requestorName = $procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? '';
@endphp

<div class="po-signatures">
    <div class="po-signature-col">
        <div class="po-signature-row">
            <span class="po-form-label">Requestor:</span>
            <span class="po-form-line">{{ $requestorName }}</span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Signature:</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Date:</span>
            <span class="po-form-line">{{ $procurementRequest->requested_at?->format('d-m-Y') ?? '' }}</span>
        </div>
    </div>
    <div class="po-signature-col">
        <div class="po-signature-row">
            <span class="po-form-label">Received by:</span>
            <span class="po-form-line">{{ $procurementRequest->received_by ?? '' }}</span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Signature:</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Date:</span>
            <span class="po-form-line"></span>
        </div>
    </div>
</div>

<div class="po-signatures pr-signatures-procurement">
    <div class="po-signature-col">
        <div class="po-signature-row">
            <span class="po-form-label">Procurement:</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Signature:</span>
            <span class="po-form-line"></span>
        </div>
        <div class="po-signature-row">
            <span class="po-form-label">Date:</span>
            <span class="po-form-line"></span>
        </div>
    </div>
    <div class="po-signature-col" aria-hidden="true"></div>
</div>
