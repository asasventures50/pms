{{-- Extended RFQ fields (vendor, quotation, payment, status). Toggle via RFQ_SHOW_EXTENDED_FIELDS --}}

@include('procurement.rfqs._quotation-lines', ['lineItems' => $lineItems])

<details class="mt-8 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm print:hidden">
    <summary class="cursor-pointer font-medium text-slate-800">Vendor recipient (optional)</summary>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="vendor_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor from system</label>
            <select name="vendor_id" id="vendor_id" data-snapshot-url="{{ url('/vendors') }}"
                    class="admin-filter-control mt-1">
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
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_contact" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Contact</label>
            <input type="text" name="vendor_contact" id="vendor_contact"
                   value="{{ old('vendor_contact', $rfq?->vendor_contact ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
            <input type="email" name="vendor_email" id="vendor_email"
                   value="{{ old('vendor_email', $rfq?->vendor_email ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_phone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
            <input type="text" name="vendor_phone" id="vendor_phone"
                   value="{{ old('vendor_phone', $rfq?->vendor_phone ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div class="md:col-span-2">
            <label for="vendor_address" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Address</label>
            <textarea name="vendor_address" id="vendor_address" rows="2" class="admin-form-textarea mt-1">{{ old('vendor_address', $rfq?->vendor_address ?? '') }}</textarea>
        </div>
    </div>
</details>

<details class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm print:hidden">
    <summary class="cursor-pointer font-medium text-slate-800">Payment &amp; vendor response (optional)</summary>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="payment_method" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment method</label>
            <input type="text" name="payment_method" id="payment_method"
                   value="{{ old('payment_method', $rfq?->payment_method ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_rep_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Representative name</label>
            <input type="text" name="vendor_rep_name" id="vendor_rep_name"
                   value="{{ old('vendor_rep_name', $rfq?->vendor_rep_name ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_rep_signature" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Signature</label>
            <input type="text" name="vendor_rep_signature" id="vendor_rep_signature"
                   value="{{ old('vendor_rep_signature', $rfq?->vendor_rep_signature ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_rep_signed_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Date</label>
            <input type="date" name="vendor_rep_signed_at" id="vendor_rep_signed_at"
                   value="{{ old('vendor_rep_signed_at', $rfq?->vendor_rep_signed_at?->format('Y-m-d') ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
        <div>
            <label for="vendor_company_stamp" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company stamp</label>
            <input type="text" name="vendor_company_stamp" id="vendor_company_stamp"
                   value="{{ old('vendor_company_stamp', $rfq?->vendor_company_stamp ?? '') }}"
                   class="admin-filter-control mt-1">
        </div>
    </div>
</details>

<details class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm print:hidden">
    <summary class="cursor-pointer font-medium text-slate-800">Internal status</summary>
    <div class="mt-4 max-w-xs">
        <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
        <select name="status" id="status" class="admin-filter-control mt-1">
            @foreach (\App\Enums\Procurement\Rfqs\RfqStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected(old('status', $rfq?->status?->value ?? 'draft') === $case->value)>{{ ucfirst($case->value) }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-slate-500">Prepared by {{ $rfq?->creator?->name ?? auth()->user()->name }}</p>
    </div>
</details>
