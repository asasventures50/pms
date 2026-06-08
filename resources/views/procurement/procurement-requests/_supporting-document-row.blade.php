@php
    $index = $index ?? 0;
    $row = $row ?? [];
@endphp

<div class="pr-document-row grid gap-3 rounded-lg border border-slate-200 bg-slate-50/50 p-3 sm:grid-cols-12">
    <div class="sm:col-span-3">
        <label class="block text-xs text-slate-500">Document type</label>
        <input type="text" name="supporting_document_rows[{{ $index }}][document_type]"
               value="{{ old("supporting_document_rows.$index.document_type", $row['document_type'] ?? '') }}"
               data-name="document_type"
               class="admin-filter-control mt-1 w-full">
    </div>
    <div class="sm:col-span-3">
        <label class="block text-xs text-slate-500">File</label>
        <input type="file" name="supporting_document_rows[{{ $index }}][file]" data-name="file"
               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.zip,.rar"
               class="mt-1 block w-full text-sm text-slate-700 file:mr-2 file:rounded-md file:border-0 file:bg-slate-900 file:px-2 file:py-1 file:text-xs file:text-white">
    </div>
    <div class="sm:col-span-3">
        <label class="block text-xs text-slate-500">Or link URL</label>
        <input type="url" name="supporting_document_rows[{{ $index }}][url]"
               value="{{ old("supporting_document_rows.$index.url", $row['url'] ?? '') }}"
               data-name="url" placeholder="https://"
               class="admin-filter-control mt-1 w-full">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs text-slate-500">File description</label>
        <input type="text" name="supporting_document_rows[{{ $index }}][file_description]"
               value="{{ old("supporting_document_rows.$index.file_description", $row['file_description'] ?? '') }}"
               data-name="file_description"
               class="admin-filter-control mt-1 w-full">
    </div>
    <div class="flex items-end sm:col-span-1 print:hidden">
        <button type="button" class="pr-remove-document-row text-sm font-medium text-red-700 hover:text-red-900">Remove</button>
    </div>
</div>
