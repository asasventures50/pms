@php
    use App\Enums\Procurement\ProcurementRequests\GeographicScope;
    use App\Enums\Procurement\ProcurementRequests\ProcurementType;
    use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
    use App\Support\Procurement\ProcurementCheckboxGroup;

    $requestorName = $procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? '';
    $formData = $formData ?? [];
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

<div class="po-section-title">PR information</div>
<div class="po-grid-2">
    <div class="po-grid-col">
        <div class="po-form-group"><span class="po-form-label">Project:</span><span class="po-form-line">@if ($procurementRequest->project){{ $procurementRequest->project->code }} — {{ $procurementRequest->project->name }}@else — @endif</span></div>
        <div class="po-form-group"><span class="po-form-label">Zone:</span><span class="po-form-line">@if ($procurementRequest->zone){{ $procurementRequest->zone->code }} — {{ $procurementRequest->zone->name }}@else — @endif</span></div>
        <div class="po-form-group"><span class="po-form-label">Category:</span><span class="po-form-line">{{ $procurementRequest->category?->name_en ?? $formData['legacy_category'] ?? '—' }}</span></div>
        <div class="po-form-group"><span class="po-form-label">Subcategory:</span><span class="po-form-line">{{ $procurementRequest->subcategory?->name_en ?? $formData['legacy_subcategory'] ?? '—' }}</span></div>
    </div>
    <div class="po-grid-col">
        <div class="po-form-group"><span class="po-form-label">Procurement type:</span><span class="po-form-line">{{ ProcurementCheckboxGroup::display($procurementRequest->procurement_types, ProcurementType::values(), fn ($v) => ProcurementType::from($v)->label()) ?: '—' }}</span></div>
        <div class="po-form-group"><span class="po-form-label">Scope:</span><span class="po-form-line">{{ GeographicScope::display($procurementRequest->geographic_scopes) ?: '—' }}</span></div>
        <div class="po-form-group"><span class="po-form-label">Vendor type:</span><span class="po-form-line">{{ ProcurementCheckboxGroup::display($procurementRequest->vendor_types, ProcurementVendorType::values(), fn ($v) => ProcurementVendorType::from($v)->label()) ?: '—' }}</span></div>
    </div>
</div>

@if (filled($procurementRequest->procurement_note))
    <div class="po-field-block pr-field-block-compact">
        <div class="po-field-label">Procurement note</div>
        <div class="po-field-value">{{ $procurementRequest->procurement_note }}</div>
    </div>
@endif
