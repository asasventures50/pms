@php
    use App\Enums\Procurement\Rfqs\RfqTermsLocale;
    use App\Services\Procurement\ScheduleOfWorks\ScheduleOfWorkPrSectionsNormalizer;

    $defaults = $formDefaults ?? [];
    $prSections = ScheduleOfWorkPrSectionsNormalizer::formDefaults(old('pr_sections', $defaults['pr_sections'] ?? null));
    $oldScopeTypes = collect(old('scope_types', $defaults['scope_types'] ?? []))->map(fn ($v) => (string) $v)->all();
    $oldItems = collect(old('items', $defaults['items'] ?? []))
        ->map(fn ($row) => [
            'project_zone' => trim((string) ($row['project_zone'] ?? '')),
            'description' => trim((string) ($row['description'] ?? '')),
            'quantity' => $row['quantity'] ?? '',
            'unit' => trim((string) ($row['unit'] ?? '')),
            'unit_price' => $row['unit_price'] ?? '',
        ])
        ->values()
        ->all();
    if ($oldItems === []) {
        $oldItems = [['project_zone' => '', 'description' => '', 'quantity' => '', 'unit' => '', 'unit_price' => '']];
    }
    $oldNotes = collect(old('notes', $defaults['notes'] ?? []))->map(fn ($note) => (string) $note)->filter()->values()->all();
    if ($oldNotes === []) {
        $oldNotes = [''];
    }
    $currencyCode = strtoupper(trim((string) old('currency_code', $defaults['currency_code'] ?? 'USD')));
    if (strlen($currencyCode) !== 3) {
        $currencyCode = 'USD';
    }
    $printLocale = old('print_locale', $defaults['print_locale'] ?? RfqTermsLocale::En->value);
    $linkedPrId = old('procurement_request_id', $defaults['procurement_request_id'] ?? '');
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
    <section class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <h2 class="text-lg font-semibold text-slate-900">Linked P.R. (optional)</h2>
        <p class="mt-1 text-sm text-slate-600">Choose a procurement request first to auto-fill matching fields below. Without a link, enter everything manually.</p>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <select name="procurement_request_id" id="procurement_request_id"
                    data-lines-url-template="{{ url('/procurement-requests/__ID__/schedule-of-work-items') }}"
                    class="admin-filter-control min-w-[14rem] flex-1 @error('procurement_request_id') border-red-500 @enderror">
                <option value="">— Not linked —</option>
                @foreach (($procurementRequestOptions ?? []) as $option)
                    <option value="{{ $option['id'] }}" @selected((string) $linkedPrId === (string) $option['id'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
            <button type="button" id="sow-import-pr-lines"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    disabled>
                Re-import from P.R.
            </button>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            Link a P.R. to import matching sections below. Empty sections are not printed.
        </p>
        @error('procurement_request_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    @include('procurement.schedule-of-works._form-pr-sections', ['prSections' => $prSections])

    <section>
        <h2 class="text-lg font-semibold text-slate-900">Scope of work <span class="text-red-600">*</span></h2>
        <p class="mt-1 text-sm text-slate-500">Select one or more. Terms &amp; conditions on print follow these scopes (same library as PO).</p>
        <div class="mt-4 flex flex-wrap gap-4">
            @foreach ($scopeOptions as $scope)
                <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                    <input type="checkbox" name="scope_types[]" value="{{ $scope->value }}"
                           data-sow-scope-checkbox
                           class="rounded border-slate-300"
                           @checked(in_array($scope->value, $oldScopeTypes, true))>
                    <span>{{ $scope->labelEn() }}</span>
                </label>
            @endforeach
        </div>
        @error('scope_types')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="mt-4 max-w-2xl">
            <label for="scope_of_work" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                Scope of work description
            </label>
            <p class="mt-0.5 text-xs text-slate-500">Same field as on the procurement request — detailed scope text for the printed document.</p>
            <textarea name="scope_of_work" id="scope_of_work" rows="5"
                      class="admin-filter-control mt-2 w-full resize-y @error('scope_of_work') border-red-500 @enderror">{{ old('scope_of_work', $defaults['scope_of_work'] ?? '') }}</textarea>
            @error('scope_of_work')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </section>

    <section>
        <h2 class="text-lg font-semibold text-slate-900">Header</h2>
        <div class="mt-4 grid max-w-3xl gap-4 sm:grid-cols-2">
            <div>
                <label for="recipient_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Recipient <span class="text-red-600">*</span></label>
                <input type="text" name="recipient_name" id="recipient_name"
                       value="{{ old('recipient_name', $defaults['recipient_name'] ?? '') }}"
                       class="admin-filter-control mt-1 w-full @error('recipient_name') border-red-500 @enderror">
                @error('recipient_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="project_manager_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Project manager</label>
                <input type="text" name="project_manager_name" id="project_manager_name"
                       value="{{ old('project_manager_name', $defaults['project_manager_name'] ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="print_locale" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Default print language <span class="text-red-600">*</span></label>
                <select name="print_locale" id="print_locale" class="admin-filter-control mt-1 w-full @error('print_locale') border-red-500 @enderror">
                    @foreach ($printLocales as $locale)
                        <option value="{{ $locale->value }}" @selected($printLocale === $locale->value)>{{ $locale->label() }}</option>
                    @endforeach
                </select>
                @error('print_locale')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
                <input type="text" name="currency_code" id="currency_code" maxlength="3"
                       value="{{ $currencyCode }}"
                       class="admin-filter-control mt-1 uppercase w-28 @error('currency_code') border-red-500 @enderror">
                @error('currency_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-lg font-semibold text-slate-900">Vendor</h2>
        <p class="mt-1 text-sm text-slate-500">Select from the vendor list (same as purchase orders).</p>
        <div class="mt-4 max-w-xl">
            @include('procurement.partials._vendor-search-select', [
                'selectedVendor' => $selectedVendor ?? null,
                'vendorSelectOptions' => $vendorSelectOptions ?? [],
            ])
            @error('vendor_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="mt-4 max-w-xl">
            <label for="vendor_company_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor name on document</label>
            <input type="text" name="vendor_company_name" id="vendor_company_name"
                   value="{{ old('vendor_company_name', $defaults['vendor_company_name'] ?? '') }}"
                   class="admin-filter-control mt-1 w-full @error('vendor_company_name') border-red-500 @enderror">
            @error('vendor_company_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </section>

    <section>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Line items <span class="text-red-600">*</span></h2>
        </div>
        <p class="mt-1 text-sm text-slate-500">Bill of quantities — same structure as P.R. BOQ.</p>

        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 w-10">#</th>
                    <th class="px-3 py-2">Project / zone</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2">Qty</th>
                    <th class="px-3 py-2">Unit</th>
                    <th class="px-3 py-2 text-right">
                        <span data-sow-price-label data-sow-price-label-base="Unit price">Unit price{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                    <th class="px-3 py-2 text-right">
                        <span data-sow-total-label data-sow-price-label-base="Total">Total{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                    <th class="px-3 py-2 w-10"></th>
                </tr>
                </thead>
                <tbody id="sow-lines-body" class="divide-y divide-slate-100">
                @foreach ($oldItems as $index => $item)
                    <tr data-sow-line-row>
                        <td class="px-3 py-2 text-center text-slate-500" data-sow-line-num>{{ $index + 1 }}</td>
                        <td class="px-3 py-2">
                            <input type="text" name="items[{{ $index }}][project_zone]" value="{{ $item['project_zone'] }}"
                                   placeholder="e.g. Qassion"
                                   class="admin-filter-control w-full min-w-[8rem]">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="items[{{ $index }}][description]" value="{{ $item['description'] }}" required
                                   data-sow-line-description
                                   class="admin-filter-control w-full min-w-[10rem] @error('items.'.$index.'.description') border-red-500 @enderror">
                            @error('items.'.$index.'.description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}"
                                   min="0" step="0.001" data-sow-line-quantity
                                   class="admin-filter-control w-24 text-right @error('items.'.$index.'.quantity') border-red-500 @enderror">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="items[{{ $index }}][unit]" value="{{ $item['unit'] }}"
                                   class="admin-filter-control w-24">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}"
                                   min="0" step="0.01" data-sow-line-unit-price
                                   class="admin-filter-control w-28 text-right @error('items.'.$index.'.unit_price') border-red-500 @enderror">
                        </td>
                        <td class="px-3 py-2 text-right font-medium text-slate-900 tabular-nums" data-sow-line-total>0.00</td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" data-sow-remove-line
                                    class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-50 @if($loop->first && count($oldItems) === 1) hidden @endif"
                                    title="Remove line">×</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" id="sow-add-line-btn"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            + Add line
        </button>
        <div class="mt-4 max-w-xs rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <div class="flex items-center justify-between gap-4">
                <span class="font-medium text-slate-700">Grand total (preview)</span>
                <span id="sow-grand-total-preview" class="font-semibold text-slate-900 tabular-nums">0.00</span>
            </div>
        </div>
        @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section>
        <h2 class="text-lg font-semibold text-slate-900">Notes</h2>
        <p class="mt-1 text-sm text-slate-500">Optional — each box is one bullet on the printed document.</p>
        <div id="sow-notes-list" class="mt-4 max-w-2xl space-y-3">
            @foreach ($oldNotes as $index => $note)
                <div class="sow-note-row flex items-start gap-2" data-sow-note-row>
                    <textarea name="notes[]" rows="3"
                              placeholder="Note {{ $index + 1 }}"
                              class="admin-filter-control sow-note-textarea min-h-[4.5rem] resize-y flex-1">{{ $note }}</textarea>
                    <button type="button" data-sow-remove-note
                            class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 @if($loop->first && count($oldNotes) === 1) hidden @endif"
                            title="Remove note">×</button>
                </div>
            @endforeach
        </div>
        <button type="button" id="sow-add-note-btn"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            + Add note
        </button>
    </section>
</div>

@include('procurement.schedule-of-works._pr-import-modal')
