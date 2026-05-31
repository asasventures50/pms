@extends('layouts.admin')

@section('title', $quotation->quotation_number)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $quotation->quotation_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">
                Vendor quotation for RFQ <a href="{{ route('rfqs.show', $rfq) }}" class="font-mono font-medium text-slate-800 hover:underline">{{ $rfq->rfq_number }}</a>
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->hasPermission('vendor-quotations.update'))
                <a href="{{ route('rfqs.quotations.edit', [$rfq, $quotation]) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            @endif
            <a href="{{ route('rfqs.show', $rfq) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to RFQ</a>
            <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Print</button>
        </div>
    </div>

    <article class="mx-auto max-w-5xl space-y-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 print:border print:shadow-none">
        <header class="border-b border-slate-200 pb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vendor quotation</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $quotation->vendor_company_name }}</h2>
        </header>

        <section class="text-sm">
            <h3 class="font-semibold uppercase tracking-wide text-slate-700">RFQ</h3>
            <dl class="mt-2 grid gap-2 sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">RFQ No.</dt><dd class="font-mono">{{ $rfq->rfq_number }}</dd></div>
                <div><dt class="text-xs text-slate-500">Issue date</dt><dd>{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Submission deadline</dt><dd>{{ $rfq->submission_deadline?->format('Y-m-d') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Quotation validity</dt><dd>{{ $rfq->quotation_validity ?? '—' }}</dd></div>
            </dl>
        </section>

        @include('procurement._our-company', [
            'document' => $rfq,
            'buyerCompany' => $buyerCompany ?? null,
            'variant' => 'rfq-doc',
        ])

        <section class="text-sm">
            <h3 class="font-semibold uppercase tracking-wide text-slate-700">Vendor</h3>
            <dl class="mt-2 grid gap-2 sm:grid-cols-2">
                <div><dt class="text-xs text-slate-500">Contact</dt><dd>{{ $quotation->vendor_contact ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Email</dt><dd>{{ $quotation->vendor_email ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Phone</dt><dd>{{ $quotation->vendor_phone ?? '—' }}</dd></div>
                @if ($quotation->vendor_address)
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Address</dt><dd class="whitespace-pre-wrap">{{ $quotation->vendor_address }}</dd></div>
                @endif
            </dl>
        </section>

        <section class="overflow-x-auto text-sm">
            <h3 class="font-semibold uppercase tracking-wide text-slate-700">Quoted items</h3>
            <table class="mt-3 min-w-full border-collapse border border-slate-200">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
                <tr>
                    <th class="border border-slate-200 px-2 py-2">Item</th>
                    <th class="border border-slate-200 px-2 py-2">Description</th>
                    <th class="border border-slate-200 px-2 py-2">Compliance</th>
                    <th class="border border-slate-200 px-2 py-2">Brand</th>
                    <th class="border border-slate-200 px-2 py-2 text-right">Unit price</th>
                    <th class="border border-slate-200 px-2 py-2">Currency</th>
                    <th class="border border-slate-200 px-2 py-2 text-right">Line total</th>
                    <th class="border border-slate-200 px-2 py-2 text-right">Tax</th>
                    <th class="border border-slate-200 px-2 py-2">Lead time</th>
                    <th class="border border-slate-200 px-2 py-2">Warranty</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($quotation->items as $line)
                    @php
                        $rfqLine = $line->rfqItem;
                        $lineGrand = (float) $line->total_price + (float) $line->tax;
                    @endphp
                    <tr>
                        <td class="border border-slate-200 px-2 py-2 font-mono text-xs">{{ $line->item_number ?? '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">
                            {{ $rfqLine?->description ?? '—' }}
                            @if ($line->alternative_if_no)
                                <p class="mt-1 text-xs text-amber-800">Alt: {{ $line->alternative_if_no }}</p>
                            @endif
                            @if ($line->item_description_if_no)
                                <p class="mt-1 text-xs text-slate-600">{{ $line->item_description_if_no }}</p>
                            @endif
                        </td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->compliance?->label() ?? '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->brand_origin ?? '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2 text-right">{{ $line->unit_price !== null ? number_format($line->unit_price, 2) : '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->currency ?? '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2 text-right font-mono">{{ number_format($line->total_price, 2) }}</td>
                        <td class="border border-slate-200 px-2 py-2 text-right font-mono">{{ number_format($line->tax, 2) }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->lead_time ?? '—' }}</td>
                        <td class="border border-slate-200 px-2 py-2">{{ $line->warranty ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="6" class="border border-slate-200 px-2 py-2 text-right">Grand total</td>
                    <td colspan="4" class="border border-slate-200 px-2 py-2 text-right font-mono">{{ number_format($quotation->grand_total ?? 0, 2) }}</td>
                </tr>
                </tfoot>
            </table>
        </section>

        <section class="text-sm">
            <h3 class="font-semibold uppercase tracking-wide text-slate-700">Payment</h3>
            <p class="mt-2">{{ $quotation->payment_method ?? '—' }}</p>
            @if ($quotation->notes)
                <p class="mt-3 text-slate-600 whitespace-pre-wrap">{{ $quotation->notes }}</p>
            @endif
        </section>

        @php
            $documents = $quotation->documents_attached ?? [];
        @endphp
        @if ($documents !== [])
            <section class="text-sm">
                <h3 class="font-semibold uppercase tracking-wide text-slate-700">Documents</h3>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($documentTypes as $docType)
                        @php $file = $documents[$docType->value] ?? null; @endphp
                        @if ($file && ! empty($file['file_path']))
                            <li>
                                {{ $docType->label() }}:
                                @if (! empty($file['file_path']))
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('s3')->url($file['file_path']) }}" target="_blank" rel="noopener" class="text-slate-800 underline">
                                        {{ $file['file_name'] ?? 'Download' }}
                                    </a>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="border-t border-slate-200 pt-4 text-sm">
            <h3 class="font-semibold uppercase tracking-wide text-slate-700">Vendor representative</h3>
            <dl class="mt-2 grid gap-2 sm:grid-cols-3">
                <div><dt class="text-xs text-slate-500">Name</dt><dd>{{ $quotation->vendor_rep_name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Signature</dt><dd>{{ $quotation->vendor_rep_signature ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">Date</dt><dd>{{ $quotation->vendor_rep_signed_at?->format('Y-m-d') ?? '—' }}</dd></div>
            </dl>
        </section>
    </article>

    @if (auth()->user()->hasPermission('vendor-quotations.update'))
        <form action="{{ route('rfqs.quotations.destroy', [$rfq, $quotation]) }}" method="post" class="mt-6 print:hidden"
              onsubmit="return confirm('Delete this vendor quotation?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-medium text-red-700 hover:text-red-900">Delete quotation</button>
        </form>
    @endif
@endsection
