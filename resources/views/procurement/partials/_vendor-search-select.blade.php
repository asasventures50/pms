@php
    $pickerVendor = $selectedVendor ?? null;
    $pickerVendorId = old('vendor_id', $pickerVendor?->id ?? '');
    if ($pickerVendorId && (! $pickerVendor || (int) $pickerVendor->id !== (int) $pickerVendorId)) {
        $pickerVendor = \App\Models\Procurement\Vendors\Vendor::query()
            ->find($pickerVendorId, ['id', 'vendor_code', 'name']);
    }
    $pickerVendorLabel = $pickerVendor
        ? trim($pickerVendor->vendor_code.' — '.$pickerVendor->name)
        : '';
@endphp

<script type="application/json" id="vendor-select-options">@json($vendorSelectOptions ?? [])</script>

<div
    class="vendor-search-select"
    data-snapshot-url="{{ url('/vendors') }}"
>
    <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $pickerVendorId }}">
    <div class="relative">
        <input
            type="text"
            id="vendor_search_input"
            value="{{ $pickerVendorLabel }}"
            placeholder="Click to browse vendors, or type to filter…"
            autocomplete="off"
            class="admin-filter-control w-full @error('vendor_id') border-red-500 @enderror"
            aria-autocomplete="list"
            aria-controls="vendor_search_results"
            aria-expanded="false"
        >
        <ul
            id="vendor_search_results"
            class="absolute z-30 mt-1 hidden max-h-60 w-full list-none overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg ring-1 ring-black/5"
            role="listbox"
        ></ul>
    </div>
    <button type="button" id="vendor_search_clear" class="mt-2 text-xs font-medium text-slate-600 hover:text-slate-900">
        Manual entry (clear vendor)
    </button>
</div>
