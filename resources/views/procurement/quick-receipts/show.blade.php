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
            @if ($receipt->isLocked())
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
                    <dd class="mt-1 text-slate-900">Limit {{ number_format($dailyLimit, 2) }} · other pending/approved on this date: {{ number_format($spentOnDate, 2) }}</dd>
                </div>
            </dl>
        </section>

        @if ($receipt->status === QuickReceiptStatus::Approved || $receipt->status === QuickReceiptStatus::Rejected)
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
                @endif
            </section>
        @endif
    </div>
@endsection
