@php
    use App\Enums\Procurement\ProcurementRequests\CompliancePrequalificationLevel;

    $ps = $prSections ?? [];
    $prInfo = $ps['pr_info'] ?? [];
    $delivery = $ps['delivery'] ?? [];
    $documents = $ps['supporting_documents'] ?? [['document_type' => '', 'file_name' => '', 'file_description' => '', 'file_url' => '']];
    $paymentTerms = $ps['payment_terms'] ?? [['milestone' => '', 'amount' => '', 'percentage' => '', 'due_upon' => '']];
    $retentions = $ps['retentions'] ?? [['retention_percent' => '', 'release_period' => '']];
    $maintenance = $ps['maintenance'] ?? [];
    $timeline = $ps['timeline'] ?? [];
    $compliance = $ps['compliance'] ?? [];

    $yesNoOptions = ['' => '—', '1' => 'Yes', '0' => 'No'];
    $selectedYesNo = static function (mixed $value) use ($yesNoOptions): string {
        if ($value === true || $value === 1 || $value === '1') {
            return '1';
        }
        if ($value === false || $value === 0 || $value === '0') {
            return '0';
        }
        return '';
    };
@endphp

<section class="space-y-6" id="sow-pr-sections">
    <div>
        <h2 class="text-lg font-semibold text-slate-900">P.R. information</h2>
        <p class="mt-1 text-sm text-slate-500">Optional — filled from linked P.R. or entered manually. Empty sections are omitted on print.</p>
    </div>

    <div class="grid max-w-4xl gap-4 sm:grid-cols-2">
        @foreach ([
            'project' => 'Project',
            'zone' => 'Zone',
            'category' => 'Category',
            'subcategory' => 'Subcategory',
            'procurement_type' => 'Procurement type',
            'geographic_scope' => 'Local / International',
            'vendor_type' => 'Vendor type',
        ] as $field => $label)
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                <input type="text" name="pr_sections[pr_info][{{ $field }}]"
                       value="{{ old('pr_sections.pr_info.'.$field, $prInfo[$field] ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
        @endforeach
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Samples required</label>
            <select name="pr_sections[pr_info][samples_required]" class="admin-filter-control mt-1 w-full">
                @foreach ($yesNoOptions as $value => $label)
                    <option value="{{ $value }}" @selected($selectedYesNo(old('pr_sections.pr_info.samples_required', $prInfo['samples_required'] ?? '')) === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-900">Delivery requirements</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Required lead time (days)</label>
                <input type="number" name="pr_sections[delivery][lead_time_days]" min="0"
                       value="{{ old('pr_sections.delivery.lead_time_days', $delivery['lead_time_days'] ?? '') }}"
                       class="admin-filter-control mt-1 w-full max-w-xs">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery location</label>
                <input type="text" name="pr_sections[delivery][location]"
                       value="{{ old('pr_sections.delivery.location', $delivery['location'] ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Flexible delivery date</label>
                <select name="pr_sections[delivery][flexible_delivery_date]" class="admin-filter-control mt-1 w-full max-w-xs">
                    @foreach ($yesNoOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedYesNo(old('pr_sections.delivery.flexible_delivery_date', $delivery['flexible_delivery_date'] ?? '')) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-slate-900">Supporting documents</h3>
            <button type="button" id="sow-add-document-row" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 hover:bg-slate-50">Add row</button>
        </div>
        <div id="sow-documents-body" class="mt-4 space-y-3">
            @foreach ($documents as $index => $row)
                <div class="sow-document-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-2" data-sow-document-row>
                    <input type="text" name="pr_sections[supporting_documents][{{ $index }}][document_type]" value="{{ $row['document_type'] ?? '' }}" placeholder="Document type" class="admin-filter-control">
                    <input type="text" name="pr_sections[supporting_documents][{{ $index }}][file_name]" value="{{ $row['file_name'] ?? '' }}" placeholder="File name" class="admin-filter-control">
                    <input type="text" name="pr_sections[supporting_documents][{{ $index }}][file_url]" value="{{ $row['file_url'] ?? '' }}" placeholder="File URL" class="admin-filter-control sm:col-span-2">
                    <input type="text" name="pr_sections[supporting_documents][{{ $index }}][file_description]" value="{{ $row['file_description'] ?? '' }}" placeholder="Description" class="admin-filter-control sm:col-span-2">
                    <button type="button" data-sow-remove-document-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-2 text-left">Remove</button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-slate-900">Payment terms</h3>
            <button type="button" id="sow-add-payment-term-row" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 hover:bg-slate-50">Add row</button>
        </div>
        <div id="sow-payment-terms-body" class="mt-4 space-y-3">
            @foreach ($paymentTerms as $index => $row)
                <div class="sow-payment-term-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-4" data-sow-payment-term-row>
                    <input type="text" name="pr_sections[payment_terms][{{ $index }}][milestone]" value="{{ $row['milestone'] ?? '' }}" placeholder="Milestone" class="admin-filter-control">
                    <input type="text" name="pr_sections[payment_terms][{{ $index }}][amount]" value="{{ $row['amount'] ?? '' }}" placeholder="Note" class="admin-filter-control">
                    <input type="text" name="pr_sections[payment_terms][{{ $index }}][percentage]" value="{{ $row['percentage'] ?? '' }}" placeholder="%" class="admin-filter-control">
                    <input type="text" name="pr_sections[payment_terms][{{ $index }}][due_upon]" value="{{ $row['due_upon'] ?? '' }}" placeholder="Due upon" class="admin-filter-control">
                    <button type="button" data-sow-remove-payment-term-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-4 text-left">Remove</button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-slate-900">Retention</h3>
            <button type="button" id="sow-add-retention-row" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 hover:bg-slate-50">Add row</button>
        </div>
        <div id="sow-retentions-body" class="mt-4 space-y-3">
            @foreach ($retentions as $index => $row)
                <div class="sow-retention-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-2" data-sow-retention-row>
                    <input type="text" name="pr_sections[retentions][{{ $index }}][retention_percent]" value="{{ $row['retention_percent'] ?? '' }}" placeholder="Retention %" class="admin-filter-control">
                    <input type="text" name="pr_sections[retentions][{{ $index }}][release_period]" value="{{ $row['release_period'] ?? '' }}" placeholder="Release period" class="admin-filter-control">
                    <button type="button" data-sow-remove-retention-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-2 text-left">Remove</button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-900">Maintenance</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">After-sale service</label>
                <select name="pr_sections[maintenance][after_sale_service_applicable]" class="admin-filter-control mt-1 w-full">
                    @foreach ($yesNoOptions as $value => $label)
                        <option value="{{ $value }}" @selected($selectedYesNo(old('pr_sections.maintenance.after_sale_service_applicable', $maintenance['after_sale_service_applicable'] ?? '')) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Warranty (years)</label>
                <input type="text" name="pr_sections[maintenance][warranty_years]"
                       value="{{ old('pr_sections.maintenance.warranty_years', $maintenance['warranty_years'] ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Warranty coverage</label>
                <input type="text" name="pr_sections[maintenance][warranty_coverage]"
                       value="{{ old('pr_sections.maintenance.warranty_coverage', $maintenance['warranty_coverage'] ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-900">Procurement timeline</h3>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr><th class="px-2 py-2 text-left">Activity</th><th class="px-2 py-2 text-left">Duration (days)</th></tr>
                </thead>
                <tbody>
                @foreach ($timeline as $index => $row)
                    <tr>
                        <td class="px-2 py-2 text-slate-800">
                            {{ $row['label'] ?? $row['activity'] ?? '' }}
                            <input type="hidden" name="pr_sections[timeline][{{ $index }}][activity]" value="{{ $row['activity'] ?? '' }}">
                            <input type="hidden" name="pr_sections[timeline][{{ $index }}][label]" value="{{ $row['label'] ?? '' }}">
                        </td>
                        <td class="px-2 py-2">
                            <input type="number" min="0" name="pr_sections[timeline][{{ $index }}][duration_days]"
                                   value="{{ old('pr_sections.timeline.'.$index.'.duration_days', $row['duration_days'] ?? '') }}"
                                   class="admin-filter-control w-28">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 p-4">
        <h3 class="text-sm font-semibold text-slate-900">Compliance requirements</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach ([
                'verification_required' => 'Required verification',
                'prequalification_required' => 'Required prequalification',
                'nda_required' => 'NDA required',
                'conflict_of_interest_required' => 'Conflict of interest',
                'commitment_compliance_required' => 'Declaration of commitment and compliance',
            ] as $field => $label)
                <div>
                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                    <select name="pr_sections[compliance][{{ $field }}]" class="admin-filter-control mt-1 w-full">
                        @foreach ($yesNoOptions as $value => $optionLabel)
                            <option value="{{ $value }}" @selected($selectedYesNo(old('pr_sections.compliance.'.$field, $compliance[$field] ?? '')) === (string) $value)>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Prequalification level</label>
                <select name="pr_sections[compliance][prequalification_level]" class="admin-filter-control mt-1 w-full">
                    <option value="">—</option>
                    @foreach (CompliancePrequalificationLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected((string) old('pr_sections.compliance.prequalification_level', $compliance['prequalification_level'] ?? '') === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</section>
