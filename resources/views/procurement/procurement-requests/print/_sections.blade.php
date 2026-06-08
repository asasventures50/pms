@php
    $formData = $formData ?? [];
    $allDocs = $procurementRequest->headerDocuments->concat($formData['legacy_item_documents'] ?? collect());
@endphp

@if ($allDocs->isNotEmpty())
    <div class="po-section-title">Supporting documents</div>
    <ul class="pr-print-list">
        @foreach ($allDocs as $document)
            <li>
                {{ $document->file_name }}
                @if ($document->document_type)<span class="pr-print-muted"> — {{ $document->document_type }}</span>@endif
                @if ($document->file_description)<span class="pr-print-muted"> — {{ $document->file_description }}</span>@endif
            </li>
        @endforeach
    </ul>
@endif

@if ($procurementRequest->paymentTerms->isNotEmpty())
    <div class="po-section-title">Payment terms</div>
    <table class="po-items-table pr-items-table pr-compact-table">
        <thead>
        <tr><th>Milestone</th><th>Amount</th><th>%</th><th>Due upon</th></tr>
        </thead>
        <tbody>
        @foreach ($procurementRequest->paymentTerms as $row)
            <tr>
                <td>{{ $row->milestone ?: '—' }}</td>
                <td>{{ $row->amount ?: '—' }}</td>
                <td>{{ $row->percentage ?? '—' }}</td>
                <td>{{ $row->due_upon ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if ($procurementRequest->retentions->isNotEmpty())
    <div class="po-section-title">Retention</div>
    <table class="po-items-table pr-items-table pr-compact-table">
        <thead>
        <tr><th>Retention %</th><th>Release period</th></tr>
        </thead>
        <tbody>
        @foreach ($procurementRequest->retentions as $row)
            <tr>
                <td>{{ $row->retention_percent ?? '—' }}</td>
                <td>{{ $row->release_period ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<div class="po-section-title">Insurance &amp; warranty</div>
<div class="po-grid-2">
    <div class="po-grid-col">
        <div class="po-form-group"><span class="po-form-label">Primary insurance:</span><span class="po-form-line">@if ($procurementRequest->primary_insurance_applicable === null)—@elseif ($procurementRequest->primary_insurance_applicable)Yes@else No @endif</span></div>
        @if ($procurementRequest->primary_insurance_requirements)
            <div class="po-form-group"><span class="po-form-label">Primary requirements:</span><span class="po-form-line">{{ $procurementRequest->primary_insurance_requirements }}</span></div>
        @endif
        <div class="po-form-group"><span class="po-form-label">Final insurance:</span><span class="po-form-line">@if ($procurementRequest->final_insurance_applicable === null)—@elseif ($procurementRequest->final_insurance_applicable)Yes@else No @endif</span></div>
        @if ($procurementRequest->final_insurance_requirements)
            <div class="po-form-group"><span class="po-form-label">Final requirements:</span><span class="po-form-line">{{ $procurementRequest->final_insurance_requirements }}</span></div>
        @endif
    </div>
    <div class="po-grid-col">
        <div class="po-form-group"><span class="po-form-label">Warranty (years):</span><span class="po-form-line">{{ $procurementRequest->warranty_years ?? '—' }}</span></div>
        <div class="po-form-group"><span class="po-form-label">Coverage:</span><span class="po-form-line">{{ $procurementRequest->warranty_coverage ?: '—' }}</span></div>
    </div>
</div>

<div class="po-section-title">Procurement timeline</div>
<table class="po-items-table pr-items-table pr-compact-table">
    <thead>
    <tr><th>Activity</th><th>Days</th></tr>
    </thead>
    <tbody>
    @foreach ($formData['timeline'] ?? [] as $row)
        <tr>
            <td>{{ $row['label'] ?? '' }}</td>
            <td>{{ $row['duration_days'] ?? '—' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
