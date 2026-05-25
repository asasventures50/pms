@php
    $documents = $documents ?? [];
@endphp

<div class="rfq-pr-documents" data-rfq-pr-documents-wrap>
    @if (count($documents) > 0)
        <ul class="rfq-pr-documents-list space-y-2" data-rfq-pr-documents-list>
            @foreach ($documents as $document)
                <li class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm">
                    <a href="{{ $document['url'] }}" target="_blank" rel="noopener"
                       class="min-w-0 truncate font-medium text-slate-900 hover:underline">
                        {{ $document['file_name'] }}
                    </a>
                    @if (! empty($document['is_link']))
                        <span class="shrink-0 rounded bg-slate-200 px-1.5 py-0.5 text-xs font-medium text-slate-600">Link</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="rfq-pr-documents-empty hidden text-sm text-slate-500" data-rfq-pr-documents-empty>—</p>
    @else
        <ul class="rfq-pr-documents-list hidden space-y-2" data-rfq-pr-documents-list></ul>
        <p class="rfq-pr-documents-empty text-sm text-slate-500" data-rfq-pr-documents-empty>—</p>
    @endif
</div>
