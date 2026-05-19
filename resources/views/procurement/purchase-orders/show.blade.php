@extends('layouts.admin')

@section('title', 'Purchase Order '.$purchaseOrder->po_number)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $purchaseOrder->po_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">Requested by {{ $purchaseOrder->creator?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->hasPermission('purchase-orders.update'))
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            @endif
            <a href="{{ route('purchase-orders.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to list</a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Print</button>
        </div>
    </div>

    <article class="mx-auto max-w-4xl rounded-xl border border-slate-200 bg-white p-8 shadow-sm print:border-0 print:shadow-none">
        <header class="flex flex-col gap-2 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
            <h1 class="text-2xl font-semibold text-slate-900">Purchase Order</h1>
            <p class="text-sm font-medium text-slate-600">Procurement Department</p>
        </header>

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Order information</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-3 text-sm">
<div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Requested by</dt>
                    <dd class="mt-1 text-slate-900">{{ $purchaseOrder->creator?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">P.O. number</dt>
                    <dd class="mt-1 font-mono text-slate-900">{{ $purchaseOrder->po_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Date</dt>
                    <dd class="mt-1 text-slate-900">{{ $purchaseOrder->ordered_at?->format('Y-m-d') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Vendor</h2>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                <div><dt class="text-xs text-slate-500">Company name</dt><dd class="text-slate-900">{{ $purchaseOrder->vendor_company_name ?? $purchaseOrder->vendor?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Contact</dt><dd class="text-slate-900">{{ $purchaseOrder->vendor_contact ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Email</dt><dd class="text-slate-900">{{ $purchaseOrder->vendor_email ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Phone</dt><dd class="text-slate-900">{{ $purchaseOrder->vendor_phone ?? '—' }}</dd></div>
<div class="sm:col-span-2"><dt class="text-xs text-slate-500">Address</dt><dd class="whitespace-pre-wrap text-slate-900">{{ $purchaseOrder->vendor_address ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="mt-8 overflow-x-auto">
            <table class="min-w-full border border-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
                <tr>
                    <th class="border border-slate-200 px-3 py-2 text-left">Item</th>
                    <th class="border border-slate-200 px-3 py-2 text-left">Item or service description</th>
                    <th class="border border-slate-200 px-3 py-2 text-right">Quantity</th>
                    <th class="border border-slate-200 px-3 py-2 text-right">Price per unit</th>
                    <th class="border border-slate-200 px-3 py-2 text-right">Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($purchaseOrder->items as $line)
                    <tr>
                        <td class="border border-slate-200 px-3 py-2 font-mono text-xs">{{ $line->item ?: '—' }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $line->description }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($line->quantity, 3) }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-right font-mono">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="border border-slate-200 px-3 py-6 text-center text-slate-500">No line items.</td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="4" class="border border-slate-200 px-3 py-2 text-right">Grand Total:</td>
                    <td class="border border-slate-200 px-3 py-2 text-right font-mono">{{ number_format($purchaseOrder->total_price ?? 0, 2) }}</td>
                </tr>
                </tfoot>
            </table>
        </section>

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Order terms</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div><dt class="text-xs text-slate-500">Payment terms</dt><dd class="whitespace-pre-wrap text-slate-900">{{ $purchaseOrder->payment_terms ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Delivery time</dt><dd class="text-slate-900">{{ $purchaseOrder->delivery_time ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Delivery location</dt><dd class="whitespace-pre-wrap text-slate-900">{{ $purchaseOrder->delivery_location ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Notes</dt><dd class="whitespace-pre-wrap text-slate-900">{{ $purchaseOrder->notes ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Approval</h2>
            <div class="mt-4 space-y-4 text-sm">
                @foreach ([
                    'Procurement' => ['signature' => $purchaseOrder->procurement_signature, 'date' => $purchaseOrder->procurement_signed_at],
                    'Finance' => ['signature' => $purchaseOrder->finance_signature, 'date' => $purchaseOrder->finance_signed_at],
                    'CEO' => ['signature' => $purchaseOrder->ceo_signature, 'date' => $purchaseOrder->ceo_signed_at],
                ] as $role => $fields)
<div class="grid gap-2 border-t border-slate-100 pt-3 first:border-0 first:pt-0 sm:grid-cols-3">
                        <p class="font-medium text-slate-800">{{ $role }}</p>
                        <p><span class="text-xs text-slate-500">Signature:</span> {{ $fields['signature'] ?? '—' }}</p>
                        <p><span class="text-xs text-slate-500">Date:</span> {{ $fields['date']?->format('Y-m-d') ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <footer class="mt-10 flex flex-col gap-1 border-t border-slate-200 pt-4 text-xs text-slate-500 sm:flex-row sm:justify-between">
            <span>Form PO — Revision: ver.01 / Date: 16-05-2026</span>
            <span>Page 1 of 1</span>
        </footer>
    </article>
@endsection
