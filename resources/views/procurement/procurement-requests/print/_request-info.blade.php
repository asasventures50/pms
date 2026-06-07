@php
    $requestorName = $procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? '';
    $statusLabel = ucfirst($procurementRequest->status->value);
@endphp

<div class="po-section-title">Request information</div>
<div class="po-grid-2">
    <div class="po-grid-col po-order-left">
        <div class="po-form-group">
            <span class="po-form-label">P.R. number:</span>
            <span class="po-form-line">{{ $procurementRequest->request_number }}</span>
        </div>
        @if ($procurementRequest->requested_at)
            <div class="po-form-group">
                <span class="po-form-label">Date:</span>
                <span class="po-form-line">{{ $procurementRequest->requested_at->format('d-m-Y') }}</span>
            </div>
        @endif
        <div class="po-form-group">
            <span class="po-form-label">Status:</span>
            <span class="po-form-line">{{ $statusLabel }}</span>
        </div>
        @if (filled($procurementRequest->classification))
            <div class="po-form-group">
                <span class="po-form-label">Classification:</span>
                <span class="po-form-line">{{ $procurementRequest->classification }}</span>
            </div>
        @endif
    </div>
    <div class="po-grid-col po-order-right">
        @if ($requestorName !== '')
            <div class="po-form-group">
                <span class="po-form-label">Requestor:</span>
                <span class="po-form-line">{{ $requestorName }}</span>
            </div>
        @endif
        @if (filled($procurementRequest->requestor_department))
            <div class="po-form-group">
                <span class="po-form-label">Department:</span>
                <span class="po-form-line">{{ $procurementRequest->requestor_department }}</span>
            </div>
        @endif
        @if (filled($procurementRequest->received_by))
            <div class="po-form-group">
                <span class="po-form-label">Received by:</span>
                <span class="po-form-line">{{ $procurementRequest->received_by }}</span>
            </div>
        @endif
    </div>
</div>

@if (filled($procurementRequest->procurement_note))
    <div class="po-field-block pr-field-block-compact">
        <div class="po-field-label">Procurement note</div>
        <div class="po-field-value">{{ $procurementRequest->procurement_note }}</div>
    </div>
@endif
