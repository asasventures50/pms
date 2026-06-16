@php
    $rfqContext = $rfqContext ?? [];
    $declarations = $declarations ?? [];
    $confirmedDeclarations = $quotation->vendor_declarations ?? [];
    $documents = $quotation->documents_attached ?? [];
    $requestLines = $rfqContext['request_lines'] ?? [];
    $supportingDocuments = $rfqContext['supporting_documents'] ?? [];

    $linesSubtotal = $quotation->items->sum(fn ($line) => (float) $line->total_price);
    $linesTax = $quotation->items->sum(fn ($line) => (float) $line->tax);
    $revision = (int) ($rfqContext['revision_number'] ?? $rfq->revision_number ?? 0);
    $signatureUrl = $quotation->vendor_rep_signature_path
        ? \Illuminate\Support\Facades\Storage::disk('s3')->url($quotation->vendor_rep_signature_path)
        : null;
@endphp

@include('procurement.vendor-quotations._document-header')

<section class="mt-4 text-sm">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">RFQ information</h3>
    <dl class="mt-3 grid gap-2 sm:grid-cols-2">
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">RFQ No.</dt><dd class="font-mono">{{ $rfq->rfq_number }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">RFQ revision No.</dt><dd>{{ $revision > 0 ? $revision : '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Quotation No.</dt><dd class="font-mono">{{ $quotation->quotation_number }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">PR / Request No.</dt><dd class="font-mono">{{ $rfqContext['pr_number'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Issue date</dt><dd>{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Submission deadline</dt><dd>{{ $rfqContext['submission_deadline_display'] ?? ($rfq->submission_deadline?->format('Y-m-d') ?? '—') }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Quotation validity</dt><dd>{{ $rfq->quotation_validity ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Procurement officer</dt><dd>{{ $rfqContext['procurement_officer'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Department / project</dt><dd>{{ $rfqContext['department'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Procurement mode</dt><dd>{{ $rfqContext['procurement_mode'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Sourcing type</dt><dd>{{ $rfqContext['sourcing_type'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Vendor category</dt><dd>{{ $rfqContext['vendor_category'] ?? '—' }}</dd></div>
    </dl>
</section>

@include('procurement._our-company', [
    'document' => $rfq,
    'buyerCompany' => $buyerCompany ?? null,
    'variant' => 'rfq-doc',
    'contactPerson' => $rfqContext['buyer_contact_person'] ?? null,
])

<section class="mt-6 text-sm">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Vendor</h3>
    <dl class="mt-3 grid gap-2 sm:grid-cols-2">
        <div class="border-b border-slate-900 pb-1 sm:col-span-2"><dt class="text-xs font-medium">Company name</dt><dd class="font-semibold">{{ $quotation->vendor_company_name }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Contact</dt><dd>{{ $quotation->vendor_contact ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Email</dt><dd>{{ $quotation->vendor_email ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Phone</dt><dd>{{ $quotation->vendor_phone ?? '—' }}</dd></div>
        @if ($quotation->vendor_address)
            <div class="border-b border-slate-900 pb-1 sm:col-span-2"><dt class="text-xs font-medium">Address</dt><dd class="whitespace-pre-wrap">{{ $quotation->vendor_address }}</dd></div>
        @endif
    </dl>
</section>

<section class="mt-6 text-sm">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Request details</h3>
    <dl class="mt-3 grid gap-2 sm:grid-cols-3">
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Delivery location</dt><dd>{{ $rfqContext['delivery_location'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Required delivery date</dt><dd>{{ $rfqContext['required_delivery_date'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Required lead time (days)</dt><dd>{{ $rfqContext['required_lead_time'] ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Samples required</dt><dd>{{ $rfqContext['samples_required'] ?? '—' }}</dd></div>
    </dl>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full border-collapse border border-slate-900 text-xs">
            <thead>
            <tr class="bg-slate-50">
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">PR Line No.</th>
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Item or service description</th>
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Quantity</th>
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Unit</th>
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Delivery location</th>
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Required delivery</th>
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Required lead time</th>
                <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Specification / scope</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($requestLines as $row)
                <tr>
                    <td class="border border-slate-900 px-2 py-2 font-mono align-top">{{ $row['line_number'] ?? '—' }}</td>
                    <td class="border border-slate-900 px-2 py-2 align-top">{{ $row['description'] ?? '—' }}</td>
                    <td class="border border-slate-900 px-2 py-2 text-right align-top">{{ isset($row['quantity']) ? number_format((float) $row['quantity'], 3) : '—' }}</td>
                    <td class="border border-slate-900 px-2 py-2 align-top">{{ $row['unit'] ?? '—' }}</td>
                    <td class="border border-slate-900 px-2 py-2 align-top">{{ $row['delivery_location'] ?? '—' }}</td>
                    <td class="border border-slate-900 px-2 py-2 align-top">{{ $row['required_delivery_date'] ?? '—' }}</td>
                    <td class="border border-slate-900 px-2 py-2 align-top">{{ $row['required_lead_time'] ?? '—' }}</td>
                    <td class="border border-slate-900 px-2 py-2 align-top whitespace-pre-wrap">{{ $row['scope_reference'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="border border-slate-900 px-2 py-4 text-center text-slate-500">No request lines.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($supportingDocuments !== [])
        <div class="mt-4">
            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-700">Supporting documents (from PR)</h4>
            <table class="mt-2 min-w-full border-collapse border border-slate-900 text-xs">
                <thead class="bg-slate-50">
                <tr>
                    <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">No.</th>
                    <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">PR line</th>
                    <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Document type</th>
                    <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">File</th>
                    <th class="border border-slate-900 px-2 py-2 text-left font-bold uppercase">Description</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($supportingDocuments as $doc)
                    <tr>
                        <td class="border border-slate-900 px-2 py-2 align-top">{{ $doc['number'] }}</td>
                        <td class="border border-slate-900 px-2 py-2 font-mono align-top">{{ $doc['line_number'] ?? '—' }}</td>
                        <td class="border border-slate-900 px-2 py-2 align-top">{{ $doc['document_type'] }}</td>
                        <td class="border border-slate-900 px-2 py-2 align-top">
                            @if (! empty($doc['url']))
                                <a href="{{ $doc['url'] }}" target="_blank" rel="noopener" class="underline">{{ $doc['file_name'] }}</a>
                            @else
                                {{ $doc['file_name'] ?? '—' }}
                            @endif
                        </td>
                        <td class="border border-slate-900 px-2 py-2 align-top">{{ $doc['file_description'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="mt-6 overflow-x-auto text-xs">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Vendor quotation</h3>
    <table class="mt-3 min-w-full border-collapse border border-slate-900">
        <thead class="bg-slate-50">
        <tr>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">#</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Compliance</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Deviation / alternative</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Description if N/C</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Brand / model</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Country of origin</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Unit price</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Currency</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Qty quoted</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Total price</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Discount</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Tax / VAT rate</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Tax amount</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Delivery</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Installation</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase text-right">Line total</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Lead time</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Warranty</th>
            <th class="border border-slate-900 px-1 py-2 font-bold uppercase">Remarks</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($quotation->items as $index => $line)
            @php
                $rfqLine = $line->rfqItem;
                $brandModel = trim(implode(' / ', array_filter([
                    $line->brand,
                    $line->model,
                    ! $line->brand && ! $line->model ? $line->brand_origin : null,
                ])));
                $lineGrand = (float) $line->total_price + (float) $line->tax + (float) ($line->delivery_charges ?? 0) + (float) ($line->installation ?? 0);
            @endphp
            <tr>
                <td class="border border-slate-900 px-1 py-2 font-mono align-top">{{ $index + 1 }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->compliance?->label() ?? '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->alternative_if_no ?? '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->item_description_if_no ?? '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $brandModel ?: '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->country_of_origin ?? '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top">{{ $line->unit_price !== null ? number_format($line->unit_price, 2) : '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->currency ?? '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top">{{ $line->quantity_quoted !== null ? number_format($line->quantity_quoted, 3) : ($rfqLine ? number_format($rfqLine->quantity, 3) : '—') }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top font-mono">{{ number_format($line->total_price, 2) }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top">{{ $line->discount ? number_format($line->discount, 2) : '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top">{{ $line->tax_rate ? number_format($line->tax_rate, 2).'%' : '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top font-mono">{{ number_format($line->tax, 2) }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top">{{ $line->delivery_charges ? number_format($line->delivery_charges, 2) : '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right align-top">{{ $line->installation ? number_format($line->installation, 2) : '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 text-right font-mono align-top">{{ number_format($lineGrand, 2) }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->lead_time ?? '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->warranty ?? '—' }}</td>
                <td class="border border-slate-900 px-1 py-2 align-top">{{ $line->remarks ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>

<section class="mt-6 text-sm">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Commercial summary</h3>
    <dl class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Subtotal excluding tax</dt><dd class="font-mono">{{ number_format($linesSubtotal, 2) }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Total discount</dt><dd class="font-mono">{{ $quotation->total_discount ? number_format($quotation->total_discount, 2) : '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Delivery charges</dt><dd class="font-mono">{{ $quotation->delivery_charges ? number_format($quotation->delivery_charges, 2) : '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Installation charges</dt><dd class="font-mono">{{ $quotation->installation_charges ? number_format($quotation->installation_charges, 2) : '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Tax / VAT total</dt><dd class="font-mono">{{ number_format($linesTax, 2) }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Grand total (incl. tax)</dt><dd class="font-mono text-lg font-bold">{{ number_format($quotation->grand_total ?? 0, 2) }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Payment terms</dt><dd>{{ $quotation->payment_method ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Delivery terms</dt><dd>{{ $quotation->delivery_terms ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Quotation valid until</dt><dd>{{ $quotation->quotation_valid_until?->format('Y-m-d') ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Price includes delivery</dt><dd>{{ $quotation->price_includes_delivery === null ? '—' : ($quotation->price_includes_delivery ? 'Yes' : 'No') }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Price includes installation</dt><dd>{{ $quotation->price_includes_installation === null ? '—' : ($quotation->price_includes_installation ? 'Yes' : 'No') }}</dd></div>
    </dl>
    @if ($quotation->after_sales_service)
        <p class="mt-3"><span class="text-xs font-medium uppercase">After-sales service:</span> {{ $quotation->after_sales_service }}</p>
    @endif
    @if ($quotation->notes)
        <p class="mt-3 whitespace-pre-wrap text-slate-700"><span class="text-xs font-medium uppercase">Note:</span> {{ $quotation->notes }}</p>
    @endif
</section>

<section class="mt-6 text-sm">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Required documents</h3>
    <ul class="mt-3 space-y-2">
        @foreach ($documentTypes as $docType)
            @php $file = $documents[$docType->value] ?? null; @endphp
            <li class="flex flex-wrap items-baseline gap-x-2 border-b border-slate-200 pb-2 text-xs">
                <span class="font-medium text-slate-800">{{ $docType->label() }}:</span>
                @if ($file && ! empty($file['file_path']))
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('s3')->url($file['file_path']) }}" target="_blank" rel="noopener" class="underline">
                        {{ $file['file_name'] ?? 'Download' }}
                    </a>
                @else
                    <span class="text-slate-500">Not attached</span>
                @endif
            </li>
        @endforeach
    </ul>
</section>

<section class="mt-6 text-sm">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Vendor declarations</h3>
    <ul class="mt-3 space-y-2">
        @foreach ($declarations as $key => $label)
            @php $isConfirmed = in_array($key, $confirmedDeclarations, true); @endphp
            <li class="flex items-start gap-2 text-xs leading-relaxed">
                <span aria-hidden="true" class="shrink-0">{{ $isConfirmed ? '☑' : '☐' }}</span>
                <span @class(['text-slate-900' => $isConfirmed, 'text-slate-500' => ! $isConfirmed])>{{ $label }}</span>
            </li>
        @endforeach
    </ul>
</section>

<section class="mt-6 border-t border-slate-900 pt-4 text-sm">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Vendor representative</h3>
    <dl class="mt-3 grid gap-2 sm:grid-cols-3">
        <div class="border-b border-slate-900 pb-1 sm:col-span-3"><dt class="text-xs font-medium">Vendor company name</dt><dd>{{ $quotation->vendor_company_name }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Representative name</dt><dd>{{ $quotation->vendor_rep_name ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Job title</dt><dd>{{ $quotation->vendor_rep_job_title ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Email</dt><dd>{{ $quotation->vendor_rep_email ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Phone</dt><dd>{{ $quotation->vendor_rep_phone ?? '—' }}</dd></div>
        <div class="border-b border-slate-900 pb-1 sm:col-span-3">
            <dt class="text-xs font-medium">Signature</dt>
            <dd class="mt-1">
                @if ($signatureUrl)
                    <img src="{{ $signatureUrl }}" alt="Vendor signature" class="max-h-16 max-w-xs border border-slate-200">
                @endif
                @if ($quotation->vendor_rep_signature)
                    <span class="{{ $signatureUrl ? 'mt-2 block' : '' }}">{{ $quotation->vendor_rep_signature }}</span>
                @elseif (! $signatureUrl)
                    —
                @endif
            </dd>
        </div>
        <div class="border-b border-slate-900 pb-1"><dt class="text-xs font-medium">Submission date</dt><dd>{{ $quotation->vendor_rep_signed_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
    </dl>
</section>
