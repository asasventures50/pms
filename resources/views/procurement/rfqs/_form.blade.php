@php
    $rfq = $rfq ?? null;
    $lineItems = old('items', $defaultItems ?? []);
@endphp

<div class="space-y-8">
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Request for Quotation</h2>
            <p class="text-sm text-slate-500">Procurement Department</p>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Prepared by</label>
                <p class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                    {{ $rfq?->creator?->name ?? auth()->user()->name }}
                </p>
            </div>
            <div>
                <label for="rfq_number" class="block text-xs font-medium uppercase tracking-wide text-slate-500">RFQ No.</label>
                <input type="text" name="rfq_number" id="rfq_number"
                       value="{{ old('rfq_number', $rfq?->rfq_number ?? ($nextCode ?? '')) }}"
                       class="admin-filter-control font-mono">
            </div>
            </div>
            <div>
                <label for="submission_deadline" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Submission deadline</label>
                <input type="date" name="submission_deadline" id="submission_deadline"
                       value="{{ old('submission_deadline', $rfq?->submission_deadline?->format('Y-m-d') ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="issue_date" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Issue date</label>
                <input type="date" name="issue_date" id="issue_date"
                       value="{{ old('issue_date', $rfq?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="admin-filter-control">
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Vendor</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="vendor_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor (from system)</label>
                <select name="vendor_id" id="vendor_id" data-snapshot-url="{{ url('/vendors') }}"
                        class="admin-filter-control">
                    <option value="">— Manual entry —</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected(old('vendor_id', $rfq?->vendor_id) == $vendor->id)>
                            {{ $vendor->vendor_code }} — {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="vendor_company_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company name</label>
                <input type="text" name="vendor_company_name" id="vendor_company_name"
                       value="{{ old('vendor_company_name', $rfq?->vendor_company_name ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="vendor_contact" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Contact</label>
                <input type="text" name="vendor_contact" id="vendor_contact"
                       value="{{ old('vendor_contact', $rfq?->vendor_contact ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="vendor_email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
                <input type="email" name="vendor_email" id="vendor_email"
                       value="{{ old('vendor_email', $rfq?->vendor_email ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="vendor_phone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
                <input type="text" name="vendor_phone" id="vendor_phone"
                       value="{{ old('vendor_phone', $rfq?->vendor_phone ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div class="md:col-span-2">
                <label for="vendor_address" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Address</label>
                <textarea name="vendor_address" id="vendor_address" rows="2" class="admin-form-textarea">{{ old('vendor_address', $rfq?->vendor_address ?? '') }}</textarea>
            </div>
        </div>
    </section>

    @include('procurement.rfqs._line-items', ['lineItems' => $lineItems])

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Payment &amp; vendor response</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label for="payment_method" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment method</label>
                <input type="text" name="payment_method" id="payment_method"
                       value="{{ old('payment_method', $rfq?->payment_method ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="vendor_rep_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor representative — Name</label>
                <input type="text" name="vendor_rep_name" id="vendor_rep_name"
                       value="{{ old('vendor_rep_name', $rfq?->vendor_rep_name ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="vendor_rep_signature" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Signature</label>
                <input type="text" name="vendor_rep_signature" id="vendor_rep_signature"
                       value="{{ old('vendor_rep_signature', $rfq?->vendor_rep_signature ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="vendor_rep_signed_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Date</label>
                <input type="date" name="vendor_rep_signed_at" id="vendor_rep_signed_at"
                       value="{{ old('vendor_rep_signed_at', $rfq?->vendor_rep_signed_at?->format('Y-m-d') ?? '') }}"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="vendor_company_stamp" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company stamp</label>
                <input type="text" name="vendor_company_stamp" id="vendor_company_stamp"
                       value="{{ old('vendor_company_stamp', $rfq?->vendor_company_stamp ?? '') }}"
                       class="admin-filter-control">
            </div>
        </div>
    </section>

    <details class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
        <summary class="cursor-pointer font-medium text-slate-700">Internal status</summary>
        <div class="mt-4 max-w-xs">
            <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
            <select name="status" id="status" class="admin-filter-control">
                @foreach (\App\Enums\Procurement\Rfqs\RfqStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('status', $rfq?->status?->value ?? 'draft') === $case->value)>{{ ucfirst($case->value) }}</option>
                @endforeach
            </select>
        </div>
    </details>
</div>

@push('scripts')
    @include('procurement.rfqs._form-scripts')
@endpush
