@extends('layouts.admin')

@section('title', 'Quick Receipts')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Quick Receipts</h1>
            <p class="mt-1 text-sm text-slate-600">One-off expenses without PR → RFQ → PO. Daily limit: {{ number_format($dailyLimit, 2) }} (used today: {{ number_format($spentToday, 2) }}, remaining: {{ number_format($remainingToday, 2) }}).</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Dashboard</a>
            @if (auth()->user()->hasPermission('quick-receipts.create'))
                <a href="{{ route('quick-receipts.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                    New Receipt
                </a>
            @endif
        </div>
    </div>

    <form method="get" action="{{ route('quick-receipts.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-[14rem] flex-1">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Code, title, category, employee"
                       class="admin-filter-control mt-1">
            </div>
            <div class="min-w-[10rem]">
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" id="status" class="admin-filter-control mt-1">
                    <option value="">All</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover">Apply</button>
            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('quick-receipts.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Code</th>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Employee</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Amount</th>
                <th class="px-4 py-3 print:hidden"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($receipts as $receipt)
                <tr>
                    <td class="px-4 py-3 font-mono text-slate-900">{{ $receipt->code }}</td>
                    <td class="px-4 py-3 text-slate-800">{{ $receipt->title }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $receipt->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $receipt->expense_date?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3">
                        @if ($receipt->status)
                            @include('procurement.quick-receipts._status-badge', ['status' => $receipt->status])
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-slate-900">{{ $receipt->formatAmount() }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ route('quick-receipts.show', $receipt) }}"
                               class="font-medium text-slate-700 hover:text-slate-900">View</a>
                            @if ($receipt->isPrintable())
                                <a href="{{ route('quick-receipts.print', $receipt) }}"
                                   class="font-medium text-slate-700 hover:text-slate-900">Print</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-500">No quick receipts yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($receipts->hasPages())
        <div class="mt-4">{{ $receipts->links() }}</div>
    @endif
@endsection
