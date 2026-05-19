@extends('layouts.admin')

@section('title', 'RFQ '.$rfq->rfq_number)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $rfq->rfq_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">Prepared by {{ $rfq->creator?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->hasPermission('rfqs.update'))
                <a href="{{ route('rfqs.edit', $rfq) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            @endif
            <a href="{{ route('rfqs.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back</a>
            <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Print</button>
        </div>
    </div>

    <article class="mx-auto max-w-5xl rounded-xl border border-slate-200 bg-white p-8 shadow-sm print:border-0 print:shadow-none">
        <header class="flex flex-col gap-2 border-b border-slate-200 pb-6 sm:flex-row sm:justify-between">
            <h2 class="text-2xl font-semibold text-slate-900">Request for Quotation</h2>
            <p class="text-sm font-medium text-slate-600">Procurement Department</p>
        </header>

        <section class="mt-8">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">RFQ No.</dt>
                    <dd class="mt-1 font-mono">{{ $rfq->rfq_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Prepared by</dt>
                    <dd class="mt-1">{{ $rfq->creator?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Submission deadline</dt>
                    <dd class="mt-1">{{ $rfq->submission_deadline?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-slate-500">Issue date</dt>
                    <dd class="mt-1">{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="mt-8">
            <h3 class="text-sm font-semibold uppercase text-slate-700">Vendor</h3>
            <dl class="mt-4 grid gap-2 sm:grid-cols-2 text-sm">
                <div><dt class="text-xs text-slate-500">Company</dt><dd>{{ $rfq->vendor_company_name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Contact</dt><dd>{{ $rfq->vendor_contact ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Email</dt><dd>{{ $rfq->vendor_email ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Phone</dt><dd>{{ $rfq->vendor_phone ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Address</dt><dd class="whitespace-pre-wrap">{{ $rfq->vendor_address ?? '—' }}</dd></div>
            </dl>
        </section>

        <section class="mt-8 overflow-x-auto">
            <table class="min-w-full border border-slate-200 text-xs sm:text-sm">
                <thead class="bg-slate-50 font-semibold uppercase text-slate-600">
                <tr>
                    <th colspan="5" class="border border-slate-200 px-2 py-2 text-center">Request details</th>
                    <th colspan="5" class="border border-slate-200 px-2 py-2 text-center">Vendor quotation</th>
                </tr>
                <tr>
                    <th class="border border-slate-200 px-2 py-1">Item</th>
                    <th class="border border-slate-200 px-2 py-1">Description</th>
                    <th class="border border-slate-200 px-2 py-1">Qty</th>
                    <th class="border border-slate-200 px-2 py-1">Unit</th>
                    <th class="border border-slate-200 px-2 py-1">Lead time</th>
                    <th class="border border-slate-200 px-2 py-1">Compliance</th>
                    <th class="border border-slate-200 px-2 py-1">Unit price</th>
                    <th class="border border-slate-200 px-2 py-1">Total</th>
                    <th class="border border-slate-200 px-2 py-1">Lead time</th>
                    <th class="border border-slate-200 px-2 py-1">Warranty</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rfq->items as $line)
                    <tr>
                        <td class="border border-slate-200 px-2 py-2 font-mono">{{ $line->item ?: '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->description }}</td>
                        <td class="border border-slate-200 px-2 py-2 text-right">{{ number_format($line->quantity, 3) }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->unit ?: '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->request_lead_time ?: '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->compliance ?: '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2 text-right">{{ $line->unit_price !== null ? number_format($line->unit_price, 2) : '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2 text-right font-mono">{{ number_format($line->line_total, 2) }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->quote_lead_time ?: '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->warranty ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="border border-slate-200 px-2 py-6 text-center text-slate-500">No line items.</td></tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="7" class="border border-slate-200 px-2 py-2 text-right">Grand Total:</td>
                    <td class="border border-slate-200 px-2 py-2 text-right font-mono">{{ number_format($rfq->grand_total ?? 0, 2) }}</td>
                    <td colspan="2" class="border border-slate-200"></td>
                </tr>
                </tfoot>
            </table>
        </section>

        <section class="mt-8 text-sm">
            <p class="font-medium text-slate-800">Payment method: <span class="font-normal">{{ $rfq->payment_method ?? '—' }}</span></p>
        </section>

        <section class="mt-8">
            <h3 class="text-sm font-semibold uppercase text-slate-700">Terms &amp; conditions</h3>
            <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-slate-700">
                @foreach ($terms as $term)
                    <li>{{ $term }}</li>
                @endforeach
            </ul>
        </section>

        <section class="mt-8 text-sm">
            <h3 class="text-sm font-semibold uppercase text-slate-700">Vendor representative</h3>
            <dl class="mt-3 grid gap-2 sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">Name</dt><dd>{{ $rfq->vendor_rep_name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Signature</dt><dd>{{ $rfq->vendor_rep_signature ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Date</dt><dd>{{ $rfq->vendor_rep_signed_at?->format('Y-m-d') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Company stamp</dt><dd>{{ $rfq->vendor_company_stamp ?? '—' }}</dd></div>
            </dl>
        </section>
    </article>
@endsection
