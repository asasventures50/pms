@extends('layouts.admin')

@section('title', 'Invoices')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Invoices</h1>
            <p class="mt-1 text-sm text-slate-600">Purchase order invoices for printing.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Dashboard</a>
            <a href="{{ route('invoices.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                Add Invoice
            </a>
        </div>
    </div>

    <form method="get" action="{{ route('invoices.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-[14rem] flex-1">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Invoice #, PO #, recipient, vendor"
                       class="admin-filter-control mt-1">
            </div>
            <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover">Apply</button>
            @if (request()->hasAny(['q']))
                <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Invoice #</th>
                <th class="px-4 py-3">PO #</th>
                <th class="px-4 py-3">Recipient</th>
                <th class="px-4 py-3">Vendor</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 print:hidden"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($invoices as $invoice)
                <tr>
                    <td class="px-4 py-3 font-mono text-slate-900">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3 font-mono text-slate-700">{{ $invoice->po_number }}</td>
                    <td class="px-4 py-3 text-slate-800">{{ $invoice->recipient_name }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $invoice->vendor_company_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $invoice->invoiced_at?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3 text-right text-slate-900">{{ $invoice->formatMoneyAmount($invoice->total_price) }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ route('invoices.show', $invoice) }}"
                               class="font-medium text-slate-700 hover:text-slate-900">View</a>
                            <a href="{{ route('invoices.edit', $invoice) }}"
                               class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            <a href="{{ route('invoices.print', $invoice) }}"
                               class="font-medium text-slate-700 hover:text-slate-900">Print</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-500">No invoices yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($invoices->hasPages())
        <div class="mt-6">{{ $invoices->links() }}</div>
    @endif
@endsection
