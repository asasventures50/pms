@extends('layouts.admin')

@php
    use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;

    $currency = $invoice->displayCurrency();
    $currencySuffix = $currency ? ' ('.$currency.')' : '';
    $feeRows = $invoice->feeRowsForEdit();
@endphp

@section('title', 'Invoice '.$invoice->invoice_number)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Invoices</a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $invoice->invoice_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">Read-only view — internal pricing includes margin percentages.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('invoices.edit', $invoice) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Edit
            </a>
            <a href="{{ route('invoices.print', $invoice) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Print
            </a>
            <a href="{{ route('invoices.export', $invoice) }}"
               class="inline-flex items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-900 hover:bg-emerald-100">
                Export Excel
            </a>
            <form action="{{ route('invoices.destroy', $invoice) }}" method="post" class="inline"
                  onsubmit="return confirm('Delete invoice {{ $invoice->invoice_number }}? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Invoice details</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Recipient</dt>
                    <dd class="mt-1 text-slate-900">{{ $invoice->recipient_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">PO #</dt>
                    <dd class="mt-1 font-mono text-slate-900">{{ $invoice->po_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Vendor</dt>
                    <dd class="mt-1 text-slate-900">{{ $invoice->vendor_company_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Date</dt>
                    <dd class="mt-1 text-slate-900">{{ $invoice->invoiced_at?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Currency</dt>
                    <dd class="mt-1 text-slate-900">{{ $currency ?? 'USD' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Total</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ $invoice->formatMoneyAmount($invoice->total_price) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Source</dt>
                    <dd class="mt-1 text-slate-900">{{ $invoice->isManual() ? 'Manual' : 'Purchase order' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Created by</dt>
                    <dd class="mt-1 text-slate-900">{{ $invoice->creator?->name ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Line items</h2>
            <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">#</th>
                        <th class="px-3 py-2">Zone</th>
                        <th class="px-3 py-2">Description</th>
                        <th class="px-3 py-2 text-right">Qty</th>
                        <th class="px-3 py-2">Unit</th>
                        <th class="px-3 py-2 text-right">Unit price{{ $currencySuffix }}</th>
                        <th class="px-3 py-2 text-right">Margin %</th>
                        <th class="px-3 py-2 text-right">Total{{ $currencySuffix }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($invoice->items as $line)
                        @php
                            $zone = $projectZoneResolver instanceof InvoiceProjectZoneResolver
                                ? $projectZoneResolver->zoneForInvoiceItem($line, $poItemsById)
                                : trim((string) ($line->project_zone ?? ''));
                        @endphp
                        <tr>
                            <td class="px-3 py-2 text-slate-600">{{ $line->line_number }}</td>
                            <td class="px-3 py-2 text-slate-700">{{ $zone !== null && $zone !== '' ? $zone : '—' }}</td>
                            <td class="px-3 py-2 text-slate-900">{{ $line->description }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-700">{{ number_format($line->quantity, 3) }}</td>
                            <td class="px-3 py-2 text-slate-700">{{ $line->unit ?: '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-700">{{ number_format($line->unit_price, 2) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-700">{{ number_format($line->margin_percentage ?? 0, 2) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-medium text-slate-900">{{ number_format($line->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-6 text-center text-slate-500">No line items.</td>
                        </tr>
                    @endforelse
                    @foreach ($feeRows as $fee)
                        <tr class="bg-slate-50/50">
                            <td class="px-3 py-2 text-slate-400">—</td>
                            <td class="px-3 py-2 text-slate-700">{{ ($fee['project_zone'] ?? '') !== '' ? $fee['project_zone'] : '—' }}</td>
                            <td class="px-3 py-2 text-slate-900">{{ $fee['description'] }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-700">{{ number_format($fee['quantity'], 3) }}</td>
                            <td class="px-3 py-2 text-slate-700">{{ ($fee['unit'] ?? '') !== '' ? $fee['unit'] : '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-700">{{ number_format($fee['unit_price'], 2) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums text-slate-700">{{ number_format($fee['margin_percentage'] ?? 0, 2) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-medium text-slate-900">{{ number_format($fee['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        @if (($notes = $invoice->displayNotes()) !== [])
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Notes</h2>
                <ul class="mt-4 list-disc space-y-2 ps-5 text-sm text-slate-700">
                    @foreach ($notes as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
