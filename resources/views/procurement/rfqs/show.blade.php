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

    <article class="rfq-document mx-auto max-w-4xl border-2 border-slate-900 bg-white p-6 text-slate-900 shadow-sm sm:p-8 print:border print:shadow-none">
        @include('procurement.rfqs._document-header')

        <div class="mt-6 space-y-4 text-sm">
            <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                <span class="shrink-0 font-medium">RFQ No:</span>
                <span class="font-mono">{{ $rfq->rfq_number }}</span>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <span class="shrink-0 font-medium">Submission Deadline:</span>
                    <span>{{ $rfq->submission_deadline?->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <span class="shrink-0 font-medium">Issue Date:</span>
                    <span>{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</span>
                </div>
            </div>
        </div>

        @include('procurement._our-company', [
            'document' => $rfq,
            'buyerCompany' => $buyerCompany ?? null,
            'variant' => 'rfq-doc',
        ])

        <section class="mt-8 overflow-x-auto">
            <h3 class="mb-2 text-sm font-bold uppercase tracking-wide text-slate-900">Request details</h3>
            <table class="min-w-full border-collapse border border-slate-900 text-sm">
                <thead>
                <tr>
                    <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase">Item</th>
                    <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase">Item or service description</th>
                    <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase w-24">Quantity</th>
                    <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase w-24">Unit</th>
                    <th class="border border-slate-900 px-2 py-2 text-left text-xs font-bold uppercase w-32">Required delivery date</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rfq->items as $line)
                    @php
                        $prDocuments = $line->procurementRequestItem?->documents ?? collect();
                    @endphp
                    <tr>
                        <td class="border border-slate-900 px-2 py-3 font-mono align-top">{{ $line->item ?: '—' }}</td>
                        <td class="border border-slate-900 px-2 py-3 align-top">{{ $line->description }}</td>
                        <td class="border border-slate-900 px-2 py-3 text-right align-top">{{ number_format($line->quantity, 3) }}</td>
                        <td class="border border-slate-900 px-2 py-3 align-top">{{ $line->unit ?: '—' }}</td>
                        <td class="border border-slate-900 px-2 py-3 align-top">{{ $line->request_lead_time ?: '—' }}</td>
                    </tr>
                    @if ($prDocuments->isNotEmpty())
                        <tr>
                            <td colspan="5" class="border border-slate-900 bg-slate-50/50 px-2 py-2 align-top text-xs">
                                <span class="font-semibold uppercase tracking-wide text-slate-600">Supporting documents (PR)</span>
                                <ul class="mt-1 space-y-1">
                                    @foreach ($prDocuments as $document)
                                        <li>
                                            <a href="{{ $document->url }}" target="_blank" rel="noopener"
                                               class="font-medium text-slate-900 underline hover:text-slate-700">
                                                {{ $document->file_name }}
                                            </a>
                                            @if (\App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument::isExternalUrl($document->file_path))
                                                <span class="text-slate-500">(link)</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="border border-slate-900 px-2 py-8 text-center text-slate-500">No line items.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </section>

        @include('procurement.rfqs._terms', ['terms' => $terms, 'rfq' => $rfq])

        @include('procurement.rfqs._payment-terms', [
            'rfq' => $rfq,
            'paymentTerms' => $paymentTerms ?? [],
        ])

        @if (config('procurement.rfq.show_extended_form_fields') && ($rfq->vendor_company_name || $rfq->vendor_contact || $rfq->vendor_email))
            <section class="mt-8 text-sm print:hidden">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Vendor recipient</h3>
                <dl class="mt-3 grid gap-2 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-500">Company</dt><dd>{{ $rfq->vendor_company_name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Contact</dt><dd>{{ $rfq->vendor_contact ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Email</dt><dd>{{ $rfq->vendor_email ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Phone</dt><dd>{{ $rfq->vendor_phone ?? '—' }}</dd></div>
                    @if ($rfq->vendor_address)
                        <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Address</dt><dd class="whitespace-pre-wrap">{{ $rfq->vendor_address }}</dd></div>
                    @endif
                </dl>
            </section>
        @endif

        @php
            $hasQuotation = $rfq->items->contains(fn ($line) => $line->unit_price !== null || $line->compliance || $line->quote_lead_time || $line->warranty);
        @endphp
        @if (config('procurement.rfq.show_extended_form_fields') && ($hasQuotation || ($rfq->grand_total ?? 0) > 0))
            <section class="mt-8 overflow-x-auto">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Vendor quotation</h3>
                <table class="mt-3 min-w-full border-collapse border border-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
                    <tr>
                        <th class="border border-slate-200 px-2 py-2">Item</th>
                        <th class="border border-slate-200 px-2 py-2">Compliance</th>
                        <th class="border border-slate-200 px-2 py-2">Unit price</th>
                        <th class="border border-slate-200 px-2 py-2">Total</th>
                        <th class="border border-slate-200 px-2 py-2">Lead time</th>
                        <th class="border border-slate-200 px-2 py-2">Warranty</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rfq->items as $line)
                        <tr>
                            <td class="border border-slate-200 px-2 py-2 font-mono">{{ $line->item ?: '—' }}</td>
                            <td class="border border-slate-200 px-2 py-2">{{ $line->compliance ?: '—' }}</td>
                            <td class="border border-slate-200 px-2 py-2 text-right">{{ $line->unit_price !== null ? number_format($line->unit_price, 2) : '—' }}</td>
                            <td class="border border-slate-200 px-2 py-2 text-right font-mono">{{ number_format($line->line_total, 2) }}</td>
                            <td class="border border-slate-200 px-2 py-2">{{ $line->quote_lead_time ?: '—' }}</td>
                            <td class="border border-slate-200 px-2 py-2">{{ $line->warranty ?: '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="bg-slate-50 font-semibold">
                        <td colspan="3" class="border border-slate-200 px-2 py-2 text-right">Grand Total:</td>
                        <td class="border border-slate-200 px-2 py-2 text-right font-mono">{{ number_format($rfq->grand_total ?? 0, 2) }}</td>
                        <td colspan="2" class="border border-slate-200"></td>
                    </tr>
                    </tfoot>
                </table>
            </section>
        @endif
    </article>
@endsection
