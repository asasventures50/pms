@php
    $canUpload = auth()->user()?->hasPermission('invoices.create') ?? false;
    $url = $invoice->signedDocumentUrl();
    $hasFile = $invoice->hasSignedDocument();
@endphp
<div class="flex flex-wrap items-center gap-1.5">
    @if ($hasFile && $url)
        <a href="{{ $url }}" target="_blank" rel="noopener"
           class="inline-flex items-center rounded-full bg-slate-800 px-2.5 py-1 text-xs font-medium text-white hover:bg-slate-900">
            View
        </a>
    @endif
    @if ($canUpload)
        <form action="{{ route('invoices.signed-document.store', $invoice) }}" method="post" enctype="multipart/form-data" class="inline">
            @csrf
            <label class="inline-flex cursor-pointer items-center rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-800 hover:bg-slate-50">
                {{ $hasFile ? 'Replace' : 'Upload' }}
                <input type="file" name="document" class="sr-only" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" onchange="this.form.submit()">
            </label>
        </form>
    @elseif (! $hasFile)
        <span class="text-slate-400">—</span>
    @endif
</div>
