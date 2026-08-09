@extends('layouts.admin')

@php
    use App\Enums\Procurement\QuickReceipts\QuickReceiptStatus;
@endphp

@section('title', $receipt->code)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('quick-receipts.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50">
                <span aria-hidden="true">←</span> Back to Quick Receipts
            </a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $receipt->code }}</h1>
            <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                @include('procurement.quick-receipts._status-badge', ['status' => $receipt->status])
                <span>{{ $receipt->title }}</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if ($canUpdate)
                <a href="{{ route('quick-receipts.edit', $receipt) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    Edit
                </a>
            @endif
            @if ($receipt->isPrintable())
                <a href="{{ route('quick-receipts.print', $receipt) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    Print
                </a>
            @endif
            @if ($canDelete ?? false)
                <form action="{{ route('quick-receipts.destroy', $receipt) }}" method="post" class="inline"
                      onsubmit="return confirm('Delete receipt {{ $receipt->code }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                        Delete
                    </button>
                </form>
            @endif
            @if ($receipt->status === QuickReceiptStatus::Signed)
                <span class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-medium text-sky-900">
                    Locked — signed receipts cannot be edited or deleted
                </span>
            @elseif ($receipt->isLocked())
                <span class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-800">
                    Locked — approved receipts cannot be edited or deleted
                </span>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        @if ($canApprove)
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Decision</h2>
                        <p class="mt-0.5 text-sm text-slate-600">Approve or reject this receipt.</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form action="{{ route('quick-receipts.approve', $receipt) }}" method="post">
                            @csrf
                            <button type="submit"
                                    class="inline-flex min-w-[8rem] items-center justify-center rounded-lg bg-emerald-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-800"
                                    onclick="return confirm('Approve {{ $receipt->code }}?');">
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('quick-receipts.reject', $receipt) }}" method="post">
                            @csrf
                            <button type="submit"
                                    class="inline-flex min-w-[8rem] items-center justify-center rounded-lg border border-red-300 bg-white px-5 py-2.5 text-sm font-medium text-red-700 hover:bg-red-50"
                                    onclick="return confirm('Reject {{ $receipt->code }}?');">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        @if ($canSign)
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Upload signed document</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Print the approved receipt, sign it, then choose the signed scan and click <strong>Save signed document</strong>.
                </p>
                @if ($receipt->hasAttachment())
                    <p class="mt-2 text-sm text-slate-600">
                        Current file:
                        <a href="{{ $receipt->attachmentUrl() }}" target="_blank" rel="noopener"
                           class="font-medium text-slate-800 underline">
                            {{ $receipt->attachment_original_name ?: 'View file' }}
                        </a>
                        (new upload replaces it)
                    </p>
                @endif

                <form action="{{ route('quick-receipts.sign', $receipt) }}" method="post" enctype="multipart/form-data" class="mt-5">
                    @csrf
                    <span class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                        Attachment <span class="text-red-600">*</span>
                    </span>
                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                        <input type="file" id="signed-attachment" name="attachment" required
                               accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf"
                               class="sr-only @error('attachment') ring-1 ring-red-500 @enderror"
                               data-qr-signed-attachment-input>
                        <label for="signed-attachment"
                               class="inline-flex w-fit cursor-pointer items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50">
                            Choose file
                        </label>
                        <span class="text-sm text-slate-500" data-qr-signed-attachment-name>No file chosen</span>
                        <button type="submit"
                                class="inline-flex w-fit items-center justify-center rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 sm:ms-auto"
                                onclick="return confirm('Save signed document and mark {{ $receipt->code }} as Signed?');">
                            Save signed document
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">JPG, PNG, WEBP, or PDF — max 10 MB.</p>
                    @error('attachment')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </form>
            </section>
        @endif

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Receipt details</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Company</dt>
                    <dd class="mt-1 text-slate-900">{{ $receipt->company()->label() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Employee</dt>
                    <dd class="mt-1 text-slate-900">{{ $receipt->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-1">
                        @include('procurement.quick-receipts._status-badge', ['status' => $receipt->status])
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Expense date</dt>
                    <dd class="mt-1 text-slate-900">{{ $receipt->expense_date?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Amount</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $receipt->formatAmount() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</dt>
                    <dd class="mt-1 text-slate-900">{{ $receipt->categoryLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Provider</dt>
                    <dd class="mt-1 text-slate-900">{{ $receipt->provider_name ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Description</dt>
                    <dd class="mt-1 text-slate-900 whitespace-pre-wrap">{{ $receipt->description ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Attachment</dt>
                    <dd class="mt-1 text-slate-900">
                        @if ($receipt->hasAttachment())
                            <a href="{{ $receipt->attachmentUrl() }}" target="_blank" rel="noopener" class="font-medium underline">
                                {{ $receipt->attachment_original_name ?: 'View file' }}
                            </a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Submitted</dt>
                    <dd class="mt-1 text-slate-900">{{ $receipt->submitted_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Daily limit context</dt>
                    <dd class="mt-1 text-slate-900">Limit {{ number_format($dailyLimit, 2) }} · other pending/approved/signed on this date: {{ number_format($spentOnDate, 2) }}</dd>
                </div>
            </dl>
        </section>

        @if (in_array($receipt->status, [QuickReceiptStatus::Approved, QuickReceiptStatus::Signed, QuickReceiptStatus::Rejected], true))
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Decision history</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Decision by</dt>
                        <dd class="mt-1 text-slate-900">{{ $receipt->approver?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Decision at</dt>
                        <dd class="mt-1 text-slate-900">{{ $receipt->approved_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                    </div>
                </dl>
                @if ($receipt->status === QuickReceiptStatus::Rejected && $canUpdate)
                    <p class="mt-4 text-sm text-slate-600">Rejected — edit the receipt to resubmit for approval.</p>
                @elseif ($receipt->status === QuickReceiptStatus::Approved && ($canSign ?? false))
                    <p class="mt-4 text-sm text-slate-600">Approved — upload the signed document above to finish.</p>
                @elseif ($receipt->status === QuickReceiptStatus::Signed)
                    <p class="mt-4 text-sm text-slate-600">Signed document is on file. Receipt is complete.</p>
                @endif
            </section>
        @endif
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('[data-qr-signed-attachment-input]');
    const nameEl = document.querySelector('[data-qr-signed-attachment-name]');
    input?.addEventListener('change', function () {
        if (!nameEl) return;
        const file = input.files?.[0];
        nameEl.textContent = file ? file.name : 'No file chosen';
        nameEl.classList.toggle('text-slate-800', !!file);
        nameEl.classList.toggle('text-slate-500', !file);
    });
});
</script>
@endpush
