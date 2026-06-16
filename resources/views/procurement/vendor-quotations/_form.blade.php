@php
    $quotation = $quotation ?? null;
    $rfqContext = $rfqContext ?? [];
    $declarations = $declarations ?? [];
    $lineItems = $lineItems ?? [];
    $supportingDocuments = $rfqContext['supporting_documents'] ?? [];
    if (is_array(old('items'))) {
        foreach ($lineItems as $i => $row) {
            if (isset(old('items')[$i]) && is_array(old('items')[$i])) {
                $lineItems[$i] = array_merge($row, old('items')[$i]);
            }
        }
    }
    $quotationNumber = old('quotation_number', $quotation?->quotation_number ?? ($nextCode ?? ''));
    $documents = $quotation?->documents_attached ?? [];
    $confirmedDeclarations = old('vendor_declarations', $quotation?->vendor_declarations ?? []);
@endphp

@error('items')<p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>@enderror

<div class="mx-auto max-w-5xl space-y-6">
    {{-- RFQ context (read-only) --}}
    <section class="rounded-xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-semibold text-slate-900">RFQ information</h2>
            <a href="{{ route('rfqs.show', $rfq) }}" class="font-mono text-sm font-medium text-slate-700 hover:text-slate-900">{{ $rfq->rfq_number }}</a>
        </div>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-xs font-medium uppercase text-slate-500">PR / request</dt><dd class="mt-0.5 font-mono">{{ $rfqContext['pr_number'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">RFQ revision</dt><dd class="mt-0.5">{{ ($rfqContext['revision_number'] ?? 0) > 0 ? $rfqContext['revision_number'] : '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Issue date</dt><dd class="mt-0.5">{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Submission deadline</dt><dd class="mt-0.5">{{ $rfqContext['submission_deadline_display'] ?? ($rfq->submission_deadline?->format('Y-m-d') ?? '—') }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Quotation validity</dt><dd class="mt-0.5">{{ $rfq->quotation_validity ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Procurement officer</dt><dd class="mt-0.5">{{ $rfqContext['procurement_officer'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Department / project</dt><dd class="mt-0.5">{{ $rfqContext['department'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Procurement mode</dt><dd class="mt-0.5">{{ $rfqContext['procurement_mode'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Sourcing type</dt><dd class="mt-0.5">{{ $rfqContext['sourcing_type'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Vendor category</dt><dd class="mt-0.5">{{ $rfqContext['vendor_category'] ?? '—' }}</dd></div>
        </dl>
    </section>

    <section class="rounded-xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Request details</h3>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
            <div><dt class="text-xs font-medium uppercase text-slate-500">Delivery location</dt><dd class="mt-0.5">{{ $rfqContext['delivery_location'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Required delivery date</dt><dd class="mt-0.5">{{ $rfqContext['required_delivery_date'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Required lead time (days)</dt><dd class="mt-0.5">{{ $rfqContext['required_lead_time'] ?? '—' }}</dd></div>
            <div><dt class="text-xs font-medium uppercase text-slate-500">Samples required</dt><dd class="mt-0.5">{{ $rfqContext['samples_required'] ?? '—' }}</dd></div>
        </dl>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-left font-semibold uppercase text-slate-600">
                <tr>
                    <th class="px-3 py-2">Line</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2">Unit</th>
                    <th class="px-3 py-2">Delivery location</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($lineItems as $row)
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-mono">{{ $row['item_number'] ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $row['description'] ?? '—' }}</td>
                        <td class="px-3 py-2 text-right">{{ isset($row['quantity']) ? number_format((float) $row['quantity'], 3) : '—' }}</td>
                        <td class="px-3 py-2">{{ $row['unit'] ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $row['delivery_location'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if ($supportingDocuments !== [])
            <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white">
                <p class="border-b border-slate-100 bg-slate-50 px-3 py-2 text-xs font-semibold uppercase text-slate-600">Supporting documents (from PR)</p>
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-left font-semibold uppercase text-slate-600">
                    <tr>
                        <th class="px-3 py-2">No.</th>
                        <th class="px-3 py-2">PR line</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">File</th>
                        <th class="px-3 py-2">Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($supportingDocuments as $doc)
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2">{{ $doc['number'] }}</td>
                            <td class="px-3 py-2 font-mono">{{ $doc['line_number'] ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $doc['document_type'] }}</td>
                            <td class="px-3 py-2">
                                @if (! empty($doc['url']))
                                    <a href="{{ $doc['url'] }}" target="_blank" rel="noopener" class="text-slate-800 underline">{{ $doc['file_name'] }}</a>
                                @else
                                    {{ $doc['file_name'] ?? '—' }}
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $doc['file_description'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Quotation header --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-lg font-semibold text-slate-900">Vendor quotation</h2>
            <p class="mt-1 text-sm text-slate-500">Enter the vendor's pricing response for each RFQ line.</p>
        </div>
        <div class="mt-4 max-w-sm">
            <label for="quotation_number" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Quotation number</label>
            <input type="text" name="quotation_number" id="quotation_number" value="{{ $quotationNumber }}"
                   class="admin-filter-control mt-1 font-mono @error('quotation_number') border-red-500 @enderror">
            @error('quotation_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </section>

    {{-- Vendor --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Vendor company</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="vendor_search_input" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor from system</label>
                <div class="mt-1">
                    @include('procurement.partials._vendor-search-select', [
                        'selectedVendor' => $selectedVendor ?? null,
                    ])
                </div>
                @error('vendor_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="vendor_company_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company name *</label>
                <input type="text" name="vendor_company_name" id="vendor_company_name" required
                       value="{{ old('vendor_company_name', $quotation?->vendor_company_name ?? '') }}"
                       class="admin-filter-control mt-1 w-full @error('vendor_company_name') border-red-500 @enderror">
                @error('vendor_company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="vendor_contact" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Contact</label>
                <input type="text" name="vendor_contact" id="vendor_contact"
                       value="{{ old('vendor_contact', $quotation?->vendor_contact ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="vendor_email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
                <input type="email" name="vendor_email" id="vendor_email"
                       value="{{ old('vendor_email', $quotation?->vendor_email ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="vendor_phone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
                <input type="text" name="vendor_phone" id="vendor_phone"
                       value="{{ old('vendor_phone', $quotation?->vendor_phone ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div class="md:col-span-2">
                <label for="vendor_address" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Address</label>
                <textarea name="vendor_address" id="vendor_address" rows="2" class="admin-form-textarea mt-1 w-full">{{ old('vendor_address', $quotation?->vendor_address ?? '') }}</textarea>
            </div>
        </div>
    </section>

    {{-- Line items --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Quoted lines</h3>
        <p class="mt-1 text-xs text-slate-500">One card per RFQ line — fill compliance, pricing, and delivery for each item.</p>
        <div id="vq-lines-body" class="mt-4 space-y-4">
            @foreach ($lineItems as $index => $row)
                @include('procurement.vendor-quotations._form-line-card', [
                    'index' => $index,
                    'row' => $row,
                    'complianceOptions' => $complianceOptions,
                ])
            @endforeach
        </div>
    </section>

    {{-- Commercial summary --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Commercial summary</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-medium uppercase text-slate-500">Subtotal (lines)</p>
                <p class="mt-1 font-mono text-lg text-slate-900" id="vq-subtotal">0.00</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                <p class="text-xs font-medium uppercase text-slate-500">Tax / VAT total</p>
                <p class="mt-1 font-mono text-lg text-slate-900" id="vq-tax-total">0.00</p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                <p class="text-xs font-medium uppercase text-emerald-800">Grand total</p>
                <p class="mt-1 font-mono text-xl font-bold text-emerald-900" id="vq-grand-total">0.00</p>
            </div>
            <div>
                <label for="total_discount" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Total discount</label>
                <input type="number" name="total_discount" id="total_discount" min="0" step="0.01"
                       value="{{ old('total_discount', $quotation?->total_discount ?? '') }}"
                       class="vq-summary-input admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="delivery_charges" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery charges</label>
                <input type="number" name="delivery_charges" id="delivery_charges" min="0" step="0.01"
                       value="{{ old('delivery_charges', $quotation?->delivery_charges ?? '') }}"
                       class="vq-summary-input admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="installation_charges" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Installation charges</label>
                <input type="number" name="installation_charges" id="installation_charges" min="0" step="0.01"
                       value="{{ old('installation_charges', $quotation?->installation_charges ?? '') }}"
                       class="vq-summary-input admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="payment_method" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment terms</label>
                <input type="text" name="payment_method" id="payment_method"
                       value="{{ old('payment_method', $quotation?->payment_method ?? '') }}"
                       class="admin-filter-control mt-1 w-full" placeholder="e.g. 30 days after invoice">
            </div>
            <div>
                <label for="delivery_terms" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery terms</label>
                <input type="text" name="delivery_terms" id="delivery_terms"
                       value="{{ old('delivery_terms', $quotation?->delivery_terms ?? '') }}"
                       class="admin-filter-control mt-1 w-full" placeholder="e.g. DAP site, Incoterms 2020">
                @error('delivery_terms')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="quotation_valid_until" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Quotation valid until</label>
                <input type="date" name="quotation_valid_until" id="quotation_valid_until"
                       value="{{ old('quotation_valid_until', $quotation?->quotation_valid_until?->format('Y-m-d') ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div class="flex flex-col justify-center gap-2">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="price_includes_delivery" value="0">
                    <input type="checkbox" name="price_includes_delivery" value="1"
                           @checked(old('price_includes_delivery', $quotation?->price_includes_delivery))
                           class="rounded border-slate-300">
                    Price includes delivery
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="price_includes_installation" value="0">
                    <input type="checkbox" name="price_includes_installation" value="1"
                           @checked(old('price_includes_installation', $quotation?->price_includes_installation))
                           class="rounded border-slate-300">
                    Price includes installation
                </label>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label for="after_sales_service" class="block text-xs font-medium uppercase tracking-wide text-slate-500">After-sales service</label>
                <textarea name="after_sales_service" id="after_sales_service" rows="2" class="admin-form-textarea mt-1 w-full">{{ old('after_sales_service', $quotation?->after_sales_service ?? '') }}</textarea>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label for="notes" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
                <textarea name="notes" id="notes" rows="2" class="admin-form-textarea mt-1 w-full">{{ old('notes', $quotation?->notes ?? '') }}</textarea>
            </div>
        </div>
    </section>

    {{-- Documents --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Required documents</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach ($documentTypes as $docType)
                @php
                    $key = $docType->value;
                    $existing = $documents[$key] ?? null;
                @endphp
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <label class="block text-sm font-medium text-slate-800">{{ $docType->label() }}</label>
                    @if ($existing)
                        <p class="mt-1 text-xs text-slate-600">
                            Current: {{ $existing['file_name'] ?? 'Uploaded file' }}
                            <label class="ml-2 inline-flex items-center gap-1 text-red-700">
                                <input type="checkbox" name="remove_documents[]" value="{{ $key }}" class="rounded border-slate-300">
                                Remove
                            </label>
                        </p>
                    @endif
                    <input type="file" name="{{ $docType->inputName() }}" class="mt-2 block w-full text-sm text-slate-600">
                </div>
            @endforeach
        </div>
    </section>

    {{-- Declarations --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Vendor declarations <span class="text-red-600">*</span></h3>
        <p class="mt-1 text-xs text-slate-500">All 7 declarations are required before saving (per quotation form specification).</p>
        @error('vendor_declarations')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="mt-4 space-y-3">
            @foreach ($declarations as $key => $label)
                <label class="flex items-start gap-3 rounded-lg border border-slate-100 bg-slate-50 p-3 text-sm text-slate-700">
                    <input type="checkbox" name="vendor_declarations[]" value="{{ $key }}" required
                           @checked(in_array($key, $confirmedDeclarations, true))
                           class="mt-0.5 rounded border-slate-300">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </section>

    {{-- Representative --}}
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Vendor representative</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label for="vendor_rep_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Representative name</label>
                <input type="text" name="vendor_rep_name" id="vendor_rep_name"
                       value="{{ old('vendor_rep_name', $quotation?->vendor_rep_name ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="vendor_rep_job_title" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Job title</label>
                <input type="text" name="vendor_rep_job_title" id="vendor_rep_job_title"
                       value="{{ old('vendor_rep_job_title', $quotation?->vendor_rep_job_title ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="vendor_rep_email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
                <input type="email" name="vendor_rep_email" id="vendor_rep_email"
                       value="{{ old('vendor_rep_email', $quotation?->vendor_rep_email ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="vendor_rep_phone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
                <input type="text" name="vendor_rep_phone" id="vendor_rep_phone"
                       value="{{ old('vendor_rep_phone', $quotation?->vendor_rep_phone ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="vendor_rep_signature" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Signature (typed name)</label>
                <input type="text" name="vendor_rep_signature" id="vendor_rep_signature"
                       value="{{ old('vendor_rep_signature', $quotation?->vendor_rep_signature ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="vendor_rep_signature_file" class="block text-xs font-medium uppercase tracking-wide text-slate-500">E-signature image</label>
                @if ($quotation?->vendor_rep_signature_path)
                    @php
                        $existingSignatureUrl = \Illuminate\Support\Facades\Storage::disk('s3')->url($quotation->vendor_rep_signature_path);
                    @endphp
                    <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <img src="{{ $existingSignatureUrl }}" alt="Current signature" class="max-h-16 object-contain">
                        <label class="mt-2 flex items-center gap-2 text-xs text-red-700">
                            <input type="hidden" name="remove_signature" value="0">
                            <input type="checkbox" name="remove_signature" value="1" class="rounded border-slate-300">
                            Remove current signature
                        </label>
                    </div>
                @endif
                <input type="file" name="vendor_rep_signature_file" id="vendor_rep_signature_file" accept="image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-slate-600">
                @error('vendor_rep_signature_file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="vendor_rep_signed_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Submission date</label>
                <input type="date" name="vendor_rep_signed_at" id="vendor_rep_signed_at"
                       value="{{ old('vendor_rep_signed_at', $quotation?->vendor_rep_signed_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
        </div>
    </section>
</div>

@push('scripts')
    @include('procurement.vendor-quotations._form-scripts')
@endpush
