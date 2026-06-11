@php
    use App\Enums\Procurement\ProcurementRequests\CompliancePrequalificationLevel;
    use App\Support\Procurement\RfqTerms;

    $generalTerms = RfqTerms::defaults(app()->getLocale());

    $boolLabel = static function (?bool $value): string {
        if ($value === null) {
            return '—';
        }

        return $value ? 'Yes' : 'No';
    };
@endphp

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

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Payment terms <span class="font-normal text-slate-500">(internal)</span></h3>
    @if ($procurementRequest->paymentTerms->isEmpty())
        <p class="mt-3 text-slate-500">—</p>
    @else
        <table class="mt-3 min-w-full text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1 pr-3">Milestone</th><th class="py-1 pr-3">Note</th><th class="py-1 pr-3">%</th><th class="py-1">Due upon</th></tr></thead>
        <tbody>@foreach ($procurementRequest->paymentTerms as $row)<tr class="border-t border-slate-100"><td class="py-2 pr-3">{{ $row->milestone ?: '—' }}</td><td class="py-2 pr-3">{{ $row->amount ?: '—' }}</td><td class="py-2 pr-3">{{ $row->percentage ?? '—' }}</td><td class="py-2">{{ $row->due_upon ?: '—' }}</td></tr>@endforeach</tbody></table>
    @endif
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Retention <span class="font-normal text-slate-500">(internal)</span></h3>
    @if ($procurementRequest->retentions->isEmpty())
        <p class="mt-3 text-slate-500">—</p>
    @else
        <table class="mt-3 min-w-full text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1 pr-3">Retention %</th><th class="py-1">Release period</th></tr></thead>
        <tbody>@foreach ($procurementRequest->retentions as $row)<tr class="border-t border-slate-100"><td class="py-2 pr-3">{{ $row->retention_percent ?? '—' }}</td><td class="py-2">{{ $row->release_period ?: '—' }}</td></tr>@endforeach</tbody></table>
    @endif
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Maintenance <span class="font-normal text-slate-500">(internal)</span></h3>
    <dl class="mt-3 grid gap-3 sm:grid-cols-2">
        <div><dt class="text-xs uppercase text-slate-500">After-sale service</dt><dd>{{ $boolLabel($procurementRequest->after_sale_service_applicable) }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Warranty (years)</dt><dd>{{ $procurementRequest->warranty_years ?? '—' }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">Coverage / scope</dt><dd>{{ $procurementRequest->warranty_coverage ?: '—' }}</dd></div>
    </dl>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Procurement timeline <span class="font-normal text-slate-500">(internal)</span></h3>
    <table class="mt-3 min-w-full text-sm"><thead class="text-xs uppercase text-slate-500"><tr><th class="py-1 pr-3">Activity</th><th class="py-1">Days</th></tr></thead>
    <tbody>
        @foreach ($formData['timeline'] ?? [] as $row)
            <tr class="border-t border-slate-100"><td class="py-2 pr-3">{{ $row['label'] ?? '' }}</td><td class="py-2">{{ $row['duration_days'] ?? '—' }}</td></tr>
        @endforeach
        <tr class="border-t border-slate-100 bg-slate-50/60">
            <td class="py-2 pr-3 font-medium">Final delivery date</td>
            <td class="py-2">
                @if (filled($procurementRequest->delivery_lead_time_days))
                    {{ $procurementRequest->delivery_lead_time_days }} days
                    <p class="mt-0.5 text-xs text-slate-500">From PO issuance date</p>
                @else
                    —
                @endif
            </td>
        </tr>
    </tbody></table>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
    <h3 class="font-semibold text-slate-900">Compliance requirements <span class="font-normal text-slate-500">(internal)</span></h3>
    <dl class="mt-3 grid gap-3 sm:grid-cols-2">
        <div><dt class="text-xs uppercase text-slate-500">Required verification</dt><dd>{{ $boolLabel($procurementRequest->compliance_verification_required) }}</dd></div>
        <div>
            <dt class="text-xs uppercase text-slate-500">Required prequalification</dt>
            <dd>{{ $boolLabel($procurementRequest->compliance_prequalification_required) }}</dd>
            @if ($procurementRequest->compliance_prequalification_required)
                <dd class="mt-1 text-xs text-slate-500">Level: {{ CompliancePrequalificationLevel::display($procurementRequest->compliance_prequalification_level?->value) }}</dd>
            @endif
        </div>
        <div><dt class="text-xs uppercase text-slate-500">NDA required</dt><dd>{{ $boolLabel($procurementRequest->nda_required) }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Conflict of interest</dt><dd>{{ $boolLabel($procurementRequest->conflict_of_interest_required) }}</dd></div>
        <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">Declaration of commitment and compliance</dt><dd>{{ $boolLabel($procurementRequest->commitment_compliance_required) }}</dd></div>
    </dl>
    @if ($generalTerms !== [])
        <div class="mt-4 border-t border-slate-100 pt-4">
            <h4 class="text-xs font-semibold uppercase text-slate-500">General terms</h4>
            <ul class="mt-2 list-none space-y-1 text-slate-700">
                @foreach ($generalTerms as $term)
                    <li class="flex gap-2"><span>-</span><span>{{ $term }}</span></li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
