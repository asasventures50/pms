<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Supporting documents</h3>
    @php
        $allDocs = $procurementRequest->headerDocuments->concat($formData['legacy_item_documents'] ?? collect());
    @endphp
    @if ($allDocs->isEmpty())
        <p class="mt-3 text-slate-500">—</p>
    @else
        <ul class="mt-3 space-y-2">
            @foreach ($allDocs as $document)
                <li>
                    <a href="{{ $document->url }}" target="_blank" rel="noopener" class="font-medium text-slate-900 underline">{{ $document->file_name }}</a>
                    @if ($document->document_type)<span class="ml-2 text-xs text-slate-500">{{ $document->document_type }}</span>@endif
                    @if ($document->file_description)<p class="text-xs text-slate-600">{{ $document->file_description }}</p>@endif
                </li>
            @endforeach
        </ul>
    @endif
</section>

@if ($procurementRequest->paymentTerms->isNotEmpty())
<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Payment terms</h3>
    <table class="mt-3 min-w-full text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1 pr-3">Milestone</th><th class="py-1 pr-3">Amount</th><th class="py-1 pr-3">%</th><th class="py-1">Due upon</th></tr></thead>
    <tbody>@foreach ($procurementRequest->paymentTerms as $row)<tr class="border-t border-slate-100"><td class="py-2 pr-3">{{ $row->milestone ?: '—' }}</td><td class="py-2 pr-3">{{ $row->amount ?: '—' }}</td><td class="py-2 pr-3">{{ $row->percentage ?? '—' }}</td><td class="py-2">{{ $row->due_upon ?: '—' }}</td></tr>@endforeach</tbody></table>
</section>
@endif

@if ($procurementRequest->retentions->isNotEmpty())
<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Retention</h3>
    <table class="mt-3 min-w-full text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1 pr-3">Retention %</th><th class="py-1">Release period</th></tr></thead>
    <tbody>@foreach ($procurementRequest->retentions as $row)<tr class="border-t border-slate-100"><td class="py-2 pr-3">{{ $row->retention_percent ?? '—' }}</td><td class="py-2">{{ $row->release_period ?: '—' }}</td></tr>@endforeach</tbody></table>
</section>
@endif

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Insurance & warranty</h3>
    <dl class="mt-3 grid gap-3 sm:grid-cols-2">
        <div><dt class="text-xs uppercase text-slate-500">Primary insurance</dt><dd>@if ($procurementRequest->primary_insurance_applicable === null)—@elseif ($procurementRequest->primary_insurance_applicable)Yes@else No @endif</dd></div>
        @if ($procurementRequest->primary_insurance_requirements)
            <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">Primary requirements</dt><dd class="mt-1 whitespace-pre-wrap">{{ $procurementRequest->primary_insurance_requirements }}</dd></div>
        @endif
        <div><dt class="text-xs uppercase text-slate-500">Final insurance</dt><dd>@if ($procurementRequest->final_insurance_applicable === null)—@elseif ($procurementRequest->final_insurance_applicable)Yes@else No @endif</dd></div>
        @if ($procurementRequest->final_insurance_requirements)
            <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">Final requirements</dt><dd class="mt-1 whitespace-pre-wrap">{{ $procurementRequest->final_insurance_requirements }}</dd></div>
        @endif
        <div><dt class="text-xs uppercase text-slate-500">Warranty (years)</dt><dd>{{ $procurementRequest->warranty_years ?? '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Coverage</dt><dd>{{ $procurementRequest->warranty_coverage ?: '—' }}</dd></div>
    </dl>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Procurement timeline</h3>
    <table class="mt-3 min-w-full text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1 pr-3">Activity</th><th class="py-1">Days</th></tr></thead>
    <tbody>@foreach ($formData['timeline'] ?? [] as $row)<tr class="border-t border-slate-100"><td class="py-2 pr-3">{{ $row['label'] ?? '' }}</td><td class="py-2">{{ $row['duration_days'] ?? '—' }}</td></tr>@endforeach</tbody></table>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Compliance & approvals</h3>
    <p class="mt-2">NDA required: @if ($procurementRequest->nda_required === null)—@elseif ($procurementRequest->nda_required)Yes@else No @endif</p>
    <table class="mt-4 min-w-full text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1 pr-3">Role</th><th class="py-1 pr-3">Name</th><th class="py-1 pr-3">Signature</th><th class="py-1">Date</th></tr></thead>
    <tbody>@foreach ($formData['approvals'] ?? [] as $row)<tr class="border-t border-slate-100"><td class="py-2 pr-3">{{ $row['label'] ?? '' }}</td><td class="py-2 pr-3">{{ $row['name'] ?: '—' }}</td><td class="py-2 pr-3">{{ $row['signature'] ?: '—' }}</td><td class="py-2">{{ $row['signed_at'] ?: '—' }}</td></tr>@endforeach</tbody></table>
</section>
