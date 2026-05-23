@php
    $index = $index ?? 0;
    $documents = $documents ?? collect();
@endphp

<div class="pr-item-supporting-docs mt-6 border-t border-slate-100 pt-4">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h4 class="text-sm font-semibold text-slate-900">Supporting documents</h4>
            <p class="mt-1 text-xs text-slate-500">Add one file at a time · max 10 MB each</p>
        </div>
        <button type="button" data-pr-item-add-supporting-file
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
            <span class="text-base leading-none" aria-hidden="true">+</span>
            Add file
        </button>
    </div>

    @if ($documents->isNotEmpty())
        <ul class="mt-4 space-y-2">
            @foreach ($documents as $document)
                <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm print:border">
                    <a href="{{ $document->url }}" target="_blank" rel="noopener"
                       class="min-w-0 truncate font-medium text-slate-900 hover:underline">
                        {{ $document->file_name }}
                    </a>
                    <label class="flex shrink-0 cursor-pointer items-center gap-2 text-xs text-slate-600 print:hidden">
                        <input type="checkbox" value="{{ $document->id }}"
                               data-pr-remove-document-id
                               name="items[{{ $index }}][remove_supporting_document_ids][]"
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                        <span>Remove</span>
                    </label>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="pr-item-supporting-files mt-4 space-y-2 print:hidden @error("items.$index.supporting_documents") rounded-lg border border-red-300 bg-red-50/30 p-3 @enderror">
        <p class="text-xs text-slate-500">PDF · Word · Excel · JPG · PNG · WebP · ZIP · RAR</p>
        <div class="pr-item-supporting-files-body space-y-2"></div>
        <template class="pr-item-supporting-file-template">
            <div class="pr-supporting-file-row flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2">
                <input type="file" data-pr-supporting-file
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.zip,.rar"
                       class="block max-w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800">
                <span class="pr-supporting-file-name min-w-0 flex-1 truncate text-sm text-slate-600"></span>
                <button type="button"
                        class="pr-remove-supporting-file shrink-0 text-sm font-medium text-red-700 hover:text-red-900">
                    Remove
                </button>
            </div>
        </template>
        @error("items.$index.supporting_documents")<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @error("items.$index.supporting_documents.*")<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
