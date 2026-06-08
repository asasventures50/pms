@php
    $po = $po ?? null;
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Vendor</h2>

    <div class="mt-4">
        <label for="vendor_search_input" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor (from system)</label>
        @include('procurement.partials._vendor-search-select', [
            'selectedVendor' => $selectedVendor ?? null,
        ])
        @error('vendor_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        <div>
            <label for="vendor_company_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Name</label>
            <input type="text" name="vendor_company_name" id="vendor_company_name"
                   value="{{ old('vendor_company_name', $po?->vendor_company_name ?? '') }}"
                   class="admin-filter-control @error('vendor_company_name') border-red-500 @enderror">
            @error('vendor_company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="vendor_email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
            <input type="email" name="vendor_email" id="vendor_email"
                   value="{{ old('vendor_email', $po?->vendor_email ?? '') }}"
                   class="admin-filter-control @error('vendor_email') border-red-500 @enderror">
            @error('vendor_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="vendor_phone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
            <input type="text" name="vendor_phone" id="vendor_phone"
                   value="{{ old('vendor_phone', $po?->vendor_phone ?? '') }}"
                   class="admin-filter-control @error('vendor_phone') border-red-500 @enderror">
            @error('vendor_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="vendor_whatsapp" class="block text-xs font-medium uppercase tracking-wide text-slate-500">WhatsApp</label>
            <input type="text" name="vendor_whatsapp" id="vendor_whatsapp"
                   value="{{ old('vendor_whatsapp', $po?->vendor_whatsapp ?? '') }}"
                   class="admin-filter-control @error('vendor_whatsapp') border-red-500 @enderror">
            @error('vendor_whatsapp')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="vendor_primary_contact_position" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Position</label>
            <input type="text" name="vendor_primary_contact_position" id="vendor_primary_contact_position"
                   value="{{ old('vendor_primary_contact_position', $po?->vendor_primary_contact_position ?? '') }}"
                   class="admin-filter-control @error('vendor_primary_contact_position') border-red-500 @enderror">
            @error('vendor_primary_contact_position')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="vendor_classification" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Classification</label>
            <input type="text" name="vendor_classification" id="vendor_classification"
                   value="{{ old('vendor_classification', $po?->vendor_classification ?? '') }}"
                   class="admin-filter-control @error('vendor_classification') border-red-500 @enderror">
            @error('vendor_classification')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>
