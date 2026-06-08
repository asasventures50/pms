@php
    $flexibleDelivery = (bool) old('flexible_delivery_date', $formDefaults['flexible_delivery_date'] ?? true);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-900">Procurement justification / business need</h3>
    <textarea name="justification" rows="4"
              class="admin-filter-control mt-3 w-full resize-y @error('justification') border-red-500 @enderror">{{ old('justification', $formDefaults['justification'] ?? '') }}</textarea>
    @error('justification')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

    <div class="mt-6 border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">Delivery requirements</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="delivery_lead_time_days" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Required delivery lead time (days)
                </label>
                <p class="mt-0.5 text-xs text-slate-500">Starting from PO issuance date</p>
                <input type="number" name="delivery_lead_time_days" id="delivery_lead_time_days"
                       value="{{ old('delivery_lead_time_days', $formDefaults['delivery_lead_time_days'] ?? '') }}"
                       min="0" max="9999"
                       class="admin-filter-control mt-2 w-full max-w-xs @error('delivery_lead_time_days') border-red-500 @enderror">
                @error('delivery_lead_time_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <label class="mt-3 flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="flexible_delivery_date" value="0">
                    <input type="checkbox" name="flexible_delivery_date" value="1" id="flexible_delivery_date"
                           @checked($flexibleDelivery)
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    <span>Flexible delivery date</span>
                </label>
            </div>
            <div>
                <label for="delivery_location" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Delivery location <span class="normal-case text-red-600">*</span>
                </label>
                <input type="text" name="delivery_location" id="delivery_location"
                       value="{{ old('delivery_location', $formDefaults['delivery_location'] ?? '') }}" required
                       class="admin-filter-control mt-2 w-full @error('delivery_location') border-red-500 @enderror">
                @error('delivery_location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="mt-6 border-t border-slate-100 pt-6">
        <label for="scope_of_work" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
            Scope of work <span class="normal-case text-slate-400">(not mandatory)</span>
        </label>
        <textarea name="scope_of_work" id="scope_of_work" rows="5"
                  class="admin-filter-control mt-2 w-full resize-y">{{ old('scope_of_work', $formDefaults['scope_of_work'] ?? '') }}</textarea>
    </div>
</section>
