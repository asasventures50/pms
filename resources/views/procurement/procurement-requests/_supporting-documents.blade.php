@php
    $headerDocuments = collect($formDefaults['header_documents'] ?? []);
    $legacyDocuments = collect($formDefaults['legacy_item_documents'] ?? []);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Supporting documents</h3>
            <p class="mt-1 text-xs text-slate-500">Document type, file, and description.</p>
        </div>
        <button type="button" id="pr-add-document-row"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
            Add document row
        </button>
    </div>

    @if ($headerDocuments->isNotEmpty())
        <ul class="mt-4 space-y-2">
            @foreach ($headerDocuments as $document)
                <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm">
                    <div class="min-w-0">
                        <a href="{{ $document->url }}" target="_blank" rel="noopener" class="font-medium text-slate-900 hover:underline">
                            {{ $document->file_name }}
                        </a>
                        @if ($document->document_type)
                            <span class="ml-2 text-xs text-slate-500">{{ $document->document_type }}</span>
                        @endif
                        @if ($document->file_description)
                            <p class="mt-0.5 text-xs text-slate-600">{{ $document->file_description }}</p>
                        @endif
                    </div>
                    <label class="flex shrink-0 cursor-pointer items-center gap-2 text-xs text-slate-600 print:hidden">
                        <input type="checkbox" name="remove_supporting_document_ids[]" value="{{ $document->id }}"
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                        <span>Remove</span>
                    </label>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($legacyDocuments->isNotEmpty())
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50/50 p-3">
            <p class="text-xs font-medium text-amber-800">Legacy line-item documents (preserved)</p>
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($legacyDocuments as $document)
                    <li>
                        <a href="{{ $document->url }}" target="_blank" rel="noopener" class="text-slate-900 hover:underline">{{ $document->file_name }}</a>
                        <label class="ml-2 print:hidden">
                            <input type="checkbox" name="remove_supporting_document_ids[]" value="{{ $document->id }}">
                            <span class="text-xs text-slate-600">Remove</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="pr-document-rows" class="mt-4 space-y-3">
        @php $docRows = old('supporting_document_rows', [['document_type' => '', 'file_description' => '', 'url' => '', 'name' => '']]); @endphp
        @foreach ($docRows as $index => $docRow)
            @include('procurement.procurement-requests._supporting-document-row', ['index' => $index, 'row' => $docRow])
        @endforeach
    </div>

    <template id="pr-document-row-template">
        @include('procurement.procurement-requests._supporting-document-row', [
            'index' => 0,
            'row' => ['document_type' => '', 'file_description' => '', 'url' => '', 'name' => ''],
        ])
    </template>
</section>
