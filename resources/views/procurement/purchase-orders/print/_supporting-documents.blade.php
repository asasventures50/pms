@php
    $supportingDocuments = $prContext['supporting_documents'] ?? [];
@endphp

@if (count($supportingDocuments) > 0)
    <div class="po-section-title">Supporting documents</div>
    <ul class="po-terms-list po-supporting-documents-list">
        @foreach ($supportingDocuments as $document)
            <li>
                {{ $document['file_name'] }}
                @if (! empty($document['document_type']))
                    <span> — {{ $document['document_type'] }}</span>
                @endif
                @if (! empty($document['file_description']))
                    <span> — {{ $document['file_description'] }}</span>
                @endif
            </li>
        @endforeach
    </ul>
@endif
