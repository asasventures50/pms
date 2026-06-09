@php
    $printLabels = $printLabels ?? \App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels::resolve(null);
    $allDocs = $procurementRequest->headerDocuments->concat($formData['legacy_item_documents'] ?? collect());
@endphp

@if ($allDocs->isNotEmpty())
    <div class="po-section-title">{{ $printLabels->t('supporting_documents') }}</div>
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
