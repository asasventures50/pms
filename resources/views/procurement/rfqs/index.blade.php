@extends('layouts.admin')

@section('title', 'RFQs')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Request for Quotation</h1>
            <p class="mt-1 text-sm text-slate-600">Manage RFQ documents sent to vendors.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if (auth()->user()->hasPermission('rfq-terms.view'))
                <a href="{{ route('rfq-terms.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    General terms
                </a>
            @endif
            @if (auth()->user()->hasPermission('rfqs.create'))
                <a href="{{ route('rfqs.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                    Add RFQ
                </a>
            @endif
        </div>
    </div>

    <form method="get" action="{{ route('rfqs.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="RFQ number, vendor, or user"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" id="status" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ ucfirst($s->value) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 flex gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('rfqs.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">RFQ No.</th>
                    <th class="px-3 py-2">Prepared by</th>
                    <th class="px-3 py-2">Vendor</th>
                    <th class="px-3 py-2">Issue date</th>
                    <th class="px-3 py-2">Deadline</th>
                    <th class="px-3 py-2">Grand total</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($rfqs as $rfq)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs">{{ $rfq->rfq_number }}</td>
                        <td class="px-3 py-2">{{ $rfq->creator?->name ?? '—' }}</td>
                        <td class="max-w-[10rem] truncate px-3 py-2" title="{{ $rfq->vendor_company_name }}">{{ $rfq->vendor_company_name ?? $rfq->vendor?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs">{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs">{{ $rfq->submission_deadline?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2">{{ $rfq->grand_total !== null ? number_format($rfq->grand_total, 2) : '—' }}</td>
                        <td class="px-3 py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium">{{ ucfirst($rfq->status->value) }}</span></td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            <a href="{{ route('rfqs.show', $rfq) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                            @if ($rfq->items_count > 0 && (auth()->user()->hasPermission('vendor-quotations.create') || auth()->user()->hasPermission('rfqs.update')))
                                <span class="mx-1 text-slate-300">|</span>
                                <a href="{{ route('rfqs.quotations.create', $rfq) }}" class="font-medium text-emerald-800 hover:text-emerald-950">Quotation</a>
                            @endif
                            @if (auth()->user()->hasPermission('rfqs.update'))
                                <span class="mx-1 text-slate-300">|</span>
                                <a href="{{ route('rfqs.edit', $rfq) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-10 text-center text-slate-500">No RFQs found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($rfqs->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">{{ $rfqs->links() }}</div>
        @endif
    </div>
@endsection
