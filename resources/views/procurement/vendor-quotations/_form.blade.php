@php
    $quotation = $quotation ?? null;
    $lineItems = $lineItems ?? [];
    if (is_array(old('items'))) {
        foreach ($lineItems as $i => $row) {
            if (isset(old('items')[$i]) && is_array(old('items')[$i])) {
                $lineItems[$i] = array_merge($row, old('items')[$i]);
            }
        }
    }
    $quotationNumber = old('quotation_number', $quotation?->quotation_number ?? ($nextCode ?? ''));
    $documents = $quotation?->documents_attached ?? [];
@endphp

@error('items')<p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>@enderror

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">RFQ reference</h2>
    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
        <div><dt class="text-xs uppercase text-slate-500">RFQ No.</dt><dd class="font-mono">{{ $rfq->rfq_number }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Issue date</dt><dd>{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Submission deadline</dt><dd>{{ $rfq->submission_deadline?->format('Y-m-d') ?? '—' }}</dd></div>
        <div><dt class="text-xs uppercase text-slate-500">Quotation validity</dt><dd>{{ $rfq->quotation_validity ?? '—' }}</dd></div>
    </dl>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Quotation</h2>
    <div class="mt-4 max-w-md">
        <label for="quotation_number" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Quotation number</label>
        <input type="text" name="quotation_number" id="quotation_number" value="{{ $quotationNumber }}"
               class="admin-filter-control mt-1 font-mono @error('quotation_number') border-red-500 @enderror">
        @error('quotation_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Vendor company</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="vendor_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor from system</label>
            <select name="vendor_id" id="vendor_id" data-snapshot-url="{{ url('/vendors') }}"
                    class="admin-filter-control mt-1 @error('vendor_id') border-red-500 @enderror">
                <option value="">— Manual entry —</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(old('vendor_id', $quotation?->vendor_id) == $vendor->id)>
                        {{ $vendor->vendor_code }} — {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="vendor_company_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company name *</label>
            <input type="text" name="vendor_company_name" id="vendor_company_name" required
                   value="{{ old('vendor_company_name', $quotation?->vendor_company_name ?? '') }}"
                   class="admin-filter-control mt-1 @error('vendor_company_name') border-red-500 @enderror">
            @error('vendor_company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="vendor_contact" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Contact</label>
            <input type="text" name="vendor_contact" id="vendor_contact"
                   value="{{ old('vendor_contact', $quotation?->vendor_contact ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
            <input type="email" name="vendor_email" id="vendor_email"
                   value="{{ old('vendor_email', $quotation?->vendor_email ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_phone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
            <input type="text" name="vendor_phone" id="vendor_phone"
                   value="{{ old('vendor_phone', $quotation?->vendor_phone ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div class="md:col-span-2">
            <label for="vendor_address" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Address</label>
            <textarea name="vendor_address" id="vendor_address" rows="2" class="admin-form-textarea mt-1">{{ old('vendor_address', $quotation?->vendor_address ?? '') }}</textarea>
        </div>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-6">
    <h3 class="text-sm font-semibold text-slate-900">Request details</h3>
    <p class="mt-1 text-xs text-slate-500">Items requested on the RFQ (read-only).</p>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full border-collapse border border-slate-200 text-sm">
            <thead class="bg-white text-xs font-semibold uppercase text-slate-600">
            <tr>
                <th class="border border-slate-200 px-2 py-2">Item</th>
                <th class="border border-slate-200 px-2 py-2">Description</th>
                <th class="border border-slate-200 px-2 py-2">Qty</th>
                <th class="border border-slate-200 px-2 py-2">Unit</th>
                <th class="border border-slate-200 px-2 py-2">Delivery location</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($lineItems as $row)
                <tr>
                    <td class="border border-slate-200 px-2 py-2 font-mono text-xs">{{ $row['item_number'] ?? '—' }}</td>
                    <td class="border border-slate-200 px-2 py-2">{{ $row['description'] ?? '—' }}</td>
                    <td class="border border-slate-200 px-2 py-2 text-right">{{ isset($row['quantity']) ? number_format((float) $row['quantity'], 3) : '—' }}</td>
                    <td class="border border-slate-200 px-2 py-2">{{ $row['unit'] ?? '—' }}</td>
                    <td class="border border-slate-200 px-2 py-2">{{ $row['delivery_location'] ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-4 sm:p-6">
    <h3 class="text-sm font-semibold text-slate-900">Vendor quotation lines</h3>
    <p class="mt-1 text-xs text-slate-500">Enter compliance, pricing, lead time, and warranty per line.</p>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full border-collapse border border-slate-200 text-sm" id="vq-lines-table">
            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
            <tr>
                <th class="border border-slate-200 px-2 py-2">#</th>
                <th class="border border-slate-200 px-2 py-2">Compliance</th>
                <th class="border border-slate-200 px-2 py-2">Alternative</th>
                <th class="border border-slate-200 px-2 py-2">Description if N/C</th>
                <th class="border border-slate-200 px-2 py-2">Brand / origin</th>
                <th class="border border-slate-200 px-2 py-2">Unit price</th>
                <th class="border border-slate-200 px-2 py-2">Currency</th>
                <th class="border border-slate-200 px-2 py-2">Line total</th>
                <th class="border border-slate-200 px-2 py-2">Tax</th>
                <th class="border border-slate-200 px-2 py-2">Lead time</th>
                <th class="border border-slate-200 px-2 py-2">Warranty</th>
            </tr>
            </thead>
            <tbody id="vq-lines-body">
            @foreach ($lineItems as $index => $row)
                @include('procurement.vendor-quotations._line-row', [
                    'index' => $index,
                    'row' => $row,
                    'complianceOptions' => $complianceOptions,
                ])
            @endforeach
            </tbody>
            <tfoot>
            <tr class="bg-slate-50 font-semibold">
                <td colspan="7" class="border border-slate-200 px-2 py-2 text-right">Grand total</td>
                <td colspan="4" class="border border-slate-200 px-2 py-2 text-right font-mono">
                    <span id="vq-grand-total">0.00</span>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Totals &amp; payment</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="payment_method" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment method</label>
            <input type="text" name="payment_method" id="payment_method"
                   value="{{ old('payment_method', $quotation?->payment_method ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div class="md:col-span-2">
            <label for="notes" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
            <textarea name="notes" id="notes" rows="2" class="admin-form-textarea mt-1">{{ old('notes', $quotation?->notes ?? '') }}</textarea>
        </div>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Required documents</h2>
    <div class="mt-4 space-y-4">
        @foreach ($documentTypes as $docType)
            @php
                $key = $docType->value;
                $existing = $documents[$key] ?? null;
                $inputName = match ($key) {
                    'commercial_registration' => 'document_commercial_registration',
                    'company_profile' => 'document_company_profile',
                    'technical_datasheet' => 'document_technical_datasheet',
                    default => 'document_'.$key,
                };
            @endphp
            <div class="rounded-lg border border-slate-100 p-3">
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
                <input type="file" name="{{ $inputName }}" class="mt-2 block w-full text-sm text-slate-600">
            </div>
        @endforeach
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-900">Vendor representative</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div>
            <label for="vendor_rep_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Name</label>
            <input type="text" name="vendor_rep_name" id="vendor_rep_name"
                   value="{{ old('vendor_rep_name', $quotation?->vendor_rep_name ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_rep_signature" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Signature</label>
            <input type="text" name="vendor_rep_signature" id="vendor_rep_signature"
                   value="{{ old('vendor_rep_signature', $quotation?->vendor_rep_signature ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_rep_signed_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Date</label>
            <input type="date" name="vendor_rep_signed_at" id="vendor_rep_signed_at"
                   value="{{ old('vendor_rep_signed_at', $quotation?->vendor_rep_signed_at?->format('Y-m-d') ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
    </div>
</section>

@push('scripts')
    @include('procurement.vendor-quotations._form-scripts')
@endpush
