@php
    use App\Support\Procurement\ProcurementScopeType;

    $index = $index ?? 0;
    $row = $row ?? [];
    $projects = $projects ?? collect();
    $selectedScopeTypes = ProcurementScopeType::selectedValues(
        old("items.$index.scope_type", $row['scope_type'] ?? null)
    );
    $selectedProjectId = old("items.$index.project_id", $row['project_id'] ?? '');
    $selectedZoneId = old("items.$index.zone_id", $row['zone_id'] ?? '');
    $flexibleDeliveryDate = (bool) old(
        "items.$index.flexible_delivery_date",
        $row['flexible_delivery_date'] ?? true
    );
    $requiredDeliveryDateValue = old("items.$index.required_delivery_date");
    if ($requiredDeliveryDateValue === null && ! empty($row['required_delivery_date'])) {
        $requiredDeliveryDateValue = $row['required_delivery_date'];
    }
    $itemDocuments = $row['documents'] ?? collect();
    $lineNo = $row['line_number'] ?? null;
    if ($lineNo === null || $lineNo === '') {
        $requestNumber = trim((string) (old('request_number') ?? $procurementRequest?->request_number ?? ($nextCode ?? '')));
        $lineNo = \App\Services\Procurement\ProcurementRequests\ProcurementRequestLineNumberFormatter::format($requestNumber, $index);
    }
@endphp

<article class="pr-line-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    @php $itemId = old("items.$index.id", $row['id'] ?? ''); @endphp
    @if ($itemId !== '' && $itemId !== null)
        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $itemId }}" data-name="id">
    @endif
    <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
        <p class="text-sm font-semibold text-slate-900">
            Line <span class="pr-line-no font-mono text-xs">{{ $lineNo ?: '—' }}</span>
        </p>
        <button type="button"
                class="pr-remove-line rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50 print:hidden"
                title="Remove line">
            Remove
        </button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                Project <span class="normal-case text-red-600">*</span>
            </label>
            <div class="mt-1 flex gap-1">
                <select name="items[{{ $index }}][project_id]" data-name="project_id" data-pr-project-select required
                        class="admin-filter-control min-w-0 flex-1 @error("items.$index.project_id") border-red-500 @enderror">
                    <option value="">—</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) $selectedProjectId === (string) $project->id)>
                            {{ $project->code }} — {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                @if (auth()->user()->hasPermission('projects.create'))
                    <button type="button" data-pr-add-project
                            class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 text-base font-medium leading-none text-slate-800 hover:bg-slate-50"
                            title="Add project">
                        +
                    </button>
                @endif
            </div>
            @error("items.$index.project_id")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Zone</label>
            <div class="mt-1 flex gap-1">
                <select name="items[{{ $index }}][zone_id]" data-name="zone_id" data-pr-zone-select
                        class="admin-filter-control min-w-0 flex-1"
                        @disabled($selectedProjectId === '' || $selectedProjectId === null)>
                    <option value="">—</option>
                    @foreach ($projects as $project)
                        @foreach ($project->zones as $zone)
                            <option value="{{ $zone->id }}"
                                    data-project-id="{{ $project->id }}"
                                    @selected((string) $selectedZoneId === (string) $zone->id)
                                    @disabled((string) $selectedProjectId !== '' && (string) $selectedProjectId !== (string) $project->id)>
                                {{ $zone->code }} — {{ $zone->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
                @if (auth()->user()->hasPermission('projects.update'))
                    <button type="button" data-pr-add-zone
                            class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 text-base font-medium leading-none text-slate-800 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            title="Add zone"
                            @disabled($selectedProjectId === '' || $selectedProjectId === null)>
                        +
                    </button>
                @endif
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                Category <span class="normal-case text-red-600">*</span>
            </label>
            <input type="text" name="items[{{ $index }}][category]" value="{{ old("items.$index.category", $row['category'] ?? '') }}"
                   data-name="category" required
                   class="admin-filter-control mt-1 w-full @error("items.$index.category") border-red-500 @enderror">
            @error("items.$index.category")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Sub category</label>
            <input type="text" name="items[{{ $index }}][subcategory]" value="{{ $row['subcategory'] ?? '' }}"
                   data-name="subcategory"
                   class="admin-filter-control mt-1 w-full">
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                Scope type <span class="normal-case text-red-600">*</span>
            </label>
            <div class="@error("items.$index.scope_type") rounded-lg ring-1 ring-red-500 @enderror">
                @include('procurement.procurement-requests._scope-type-picker', [
                    'pickerIndex' => $index,
                    'selectedScopeTypes' => $selectedScopeTypes,
                ])
            </div>
            @error("items.$index.scope_type")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error("items.$index.scope_type.*")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
            Item description <span class="normal-case text-red-600">*</span>
        </label>
        <input type="text" name="items[{{ $index }}][description]" value="{{ $row['description'] ?? '' }}"
               data-name="description" required
               class="admin-filter-control mt-1 w-full">
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-3">
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Unit</label>
            <input type="text" name="items[{{ $index }}][unit]" value="{{ $row['unit'] ?? '' }}"
                   data-name="unit"
                   class="admin-filter-control mt-1 w-full">
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Quantity</label>
            <input type="number" name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}"
                   min="0" step="0.001" data-name="quantity" required
                   class="admin-filter-control mt-1 w-full">
        </div>
        <div class="sm:col-span-1">
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Justification</label>
            <input type="text" name="items[{{ $index }}][justification]" value="{{ $row['justification'] ?? '' }}"
                   data-name="justification"
                   class="admin-filter-control mt-1 w-full">
        </div>
    </div>

    <div class="mt-6 border-t border-slate-100 pt-4">
        <h4 class="text-sm font-semibold text-slate-900">Delivery requirements</h4>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Required delivery date</label>
                <input type="date" name="items[{{ $index }}][required_delivery_date]"
                       value="{{ $requiredDeliveryDateValue ?? '' }}"
                       data-name="required_delivery_date"
                       class="admin-filter-control mt-1 w-full max-w-xs @error("items.$index.required_delivery_date") border-red-500 @enderror">
                @error("items.$index.required_delivery_date")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <label class="mt-3 flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="items[{{ $index }}][flexible_delivery_date]" value="1"
                           data-name="flexible_delivery_date"
                           @checked($flexibleDeliveryDate)
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    <span>Flexible delivery date</span>
                </label>
                <p class="mt-1 text-xs text-slate-500">When enabled, a fixed delivery date is optional.</p>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Delivery location <span class="normal-case text-red-600">*</span>
                </label>
                <input type="text" name="items[{{ $index }}][delivery_location]"
                       value="{{ old("items.$index.delivery_location", $row['delivery_location'] ?? '') }}"
                       data-name="delivery_location" required
                       class="admin-filter-control mt-1 w-full @error("items.$index.delivery_location") border-red-500 @enderror">
                @error("items.$index.delivery_location")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    @include('procurement.procurement-requests._line-item-supporting-documents', [
        'index' => $index,
        'documents' => $itemDocuments,
    ])
</article>
