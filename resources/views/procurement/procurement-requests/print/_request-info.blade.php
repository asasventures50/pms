@php
    use App\Enums\Procurement\PrCompany;

    $printLabels = $printLabels ?? \App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels::resolve(null);
    $requestorName = $procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? '';
    $formData = $formData ?? [];
    $emDash = $printLabels->t('em_dash');
@endphp

<div class="po-section-title">{{ $printLabels->t('request_information') }}</div>
<div class="po-grid-2">
    <div class="po-grid-col po-order-left">
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('pr_number') }}</span>
            <span class="po-form-line">{{ $procurementRequest->request_number }}</span>
        </div>
        @if ($procurementRequest->requested_at)
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('date') }}</span>
                <span class="po-form-line">{{ $procurementRequest->requested_at->format('d-m-Y') }}</span>
            </div>
        @endif
        @if (filled($procurementRequest->classification))
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('classification') }}</span>
                <span class="po-form-line">{{ $procurementRequest->classification }}</span>
            </div>
        @endif
    </div>
    <div class="po-grid-col po-order-right">
        @if ($requestorName !== '')
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('requestor') }}</span>
                <span class="po-form-line">{{ $requestorName }}</span>
            </div>
        @endif
        @if (filled($procurementRequest->requestor_department))
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('department_label') }}</span>
                <span class="po-form-line">{{ $procurementRequest->requestor_department }}</span>
            </div>
        @endif
        @if (filled($procurementRequest->received_by))
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('received_by') }}</span>
                <span class="po-form-line">{{ $procurementRequest->received_by }}</span>
            </div>
        @endif
    </div>
</div>

<div class="po-section-title">{{ $printLabels->t('pr_information') }}</div>
<div class="po-grid-2">
    <div class="po-grid-col">
        <div class="po-form-group"><span class="po-form-label">{{ $printLabels->t('company') }}</span><span class="po-form-line">{{ PrCompany::resolve($procurementRequest->company_key)->label() }}</span></div>
        <div class="po-form-group"><span class="po-form-label">{{ $printLabels->t('project') }}</span><span class="po-form-line">@if ($procurementRequest->project){{ $procurementRequest->project->code }} — {{ $procurementRequest->project->name }}@else {{ $emDash }} @endif</span></div>
        <div class="po-form-group"><span class="po-form-label">{{ $printLabels->t('zone') }}</span><span class="po-form-line">@if ($procurementRequest->zone){{ $procurementRequest->zone->code }} — {{ $procurementRequest->zone->name }}@else {{ $emDash }} @endif</span></div>
        <div class="po-form-group"><span class="po-form-label">{{ $printLabels->t('category') }}</span><span class="po-form-line">{{ $printLabels->categoryName($procurementRequest->category, $formData['legacy_category'] ?? null) }}</span></div>
        <div class="po-form-group"><span class="po-form-label">{{ $printLabels->t('subcategory') }}</span><span class="po-form-line">{{ $printLabels->subcategoryName($procurementRequest->subcategory, $formData['legacy_subcategory'] ?? null) }}</span></div>
    </div>
    <div class="po-grid-col">
        @include('procurement.procurement-requests.print._checkbox-group', [
            'label' => $printLabels->t('procurement_type'),
            'options' => $printLabels->procurementTypeCheckboxOptions($procurementRequest->procurement_types),
        ])
        @include('procurement.procurement-requests.print._checkbox-group', [
            'label' => $printLabels->t('local_international'),
            'required' => true,
            'hint' => $printLabels->geographicScopeBothSelected($procurementRequest->geographic_scopes)
                ? $printLabels->t('local_international_hint')
                : null,
            'options' => $printLabels->geographicScopeCheckboxOptions($procurementRequest->geographic_scopes),
        ])
        @include('procurement.procurement-requests.print._checkbox-group', [
            'label' => $printLabels->t('vendor_type'),
            'options' => $printLabels->vendorTypeCheckboxOptions($procurementRequest->vendor_types),
        ])
    </div>
</div>

@if (filled($procurementRequest->procurement_note))
    <div class="po-field-block pr-field-block-compact">
        <div class="po-field-label">{{ $printLabels->t('procurement_note') }}</div>
        <div class="po-field-value">{{ $procurementRequest->procurement_note }}</div>
    </div>
@endif
