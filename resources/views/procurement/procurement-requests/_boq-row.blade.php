@php
    $index = $index ?? 0;
    $row = $row ?? [];
    $projects = $projects ?? collect();
    $categories = $categories ?? collect();
    $selectedProjectId = $selectedProjectId ?? '';
    $canQuickAddSubcategory = $canQuickAddSubcategory ?? false;
    $selectedZoneId = old("items.$index.zone_id", $row['zone_id'] ?? '');
    $selectedCategoryId = old("items.$index.category_id", $row['category_id'] ?? '');
    $selectedSubcategoryId = old("items.$index.subcategory_id", $row['subcategory_id'] ?? '');
    $legacyCategory = $row['legacy_category'] ?? '';
    $legacySubcategory = $row['legacy_subcategory'] ?? '';
    $itemId = old("items.$index.id", $row['id'] ?? '');
    $hasProject = $selectedProjectId !== '' && $selectedProjectId !== null;
    $hasCategory = $selectedCategoryId !== '' && $selectedCategoryId !== null;
    $selectedZoneLabel = '';
    if ($selectedZoneId !== '' && $selectedZoneId !== null) {
        foreach ($projects as $project) {
            $match = $project->zones->firstWhere('id', (int) $selectedZoneId);
            if ($match) {
                $selectedZoneLabel = $match->name;
                if (filled($match->code) && $match->code !== $match->name) {
                    $selectedZoneLabel .= ' ('.$match->code.')';
                }
                break;
            }
        }
    }
@endphp

<tr class="pr-boq-row">
    <td class="px-2 py-2 align-top">
        @if ($itemId !== '' && $itemId !== null)
            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $itemId }}" data-name="id">
        @endif
        <input type="text" name="items[{{ $index }}][item_name]" value="{{ old("items.$index.item_name", $row['item_name'] ?? '') }}"
               data-name="item_name"
               class="admin-filter-control w-full min-w-[5rem]">
    </td>
    <td class="px-2 py-2 align-top pr-boq-zone-cell">
        <div class="flex w-full min-w-[11rem] gap-1">
            <select name="items[{{ $index }}][zone_id]" data-name="zone_id" data-pr-zone-select
                    title="{{ $selectedZoneLabel }}"
                    class="admin-filter-control w-full min-w-[9rem] max-w-[16rem] text-sm @error("items.$index.zone_id") border-red-500 @enderror"
                    @disabled(! $hasProject)>
                <option value="">{{ $hasProject ? '— Zone —' : '— Project first —' }}</option>
                @foreach ($projects as $project)
                    @foreach ($project->zones as $zone)
                        @php
                            $zoneLabel = $zone->name;
                            if (filled($zone->code) && $zone->code !== $zone->name) {
                                $zoneLabel .= ' ('.$zone->code.')';
                            }
                        @endphp
                        <option value="{{ $zone->id }}"
                                data-project-id="{{ $project->id }}"
                                data-zone-label="{{ $zoneLabel }}"
                                @selected((string) $selectedZoneId === (string) $zone->id)
                                @disabled((string) $selectedProjectId !== '' && (string) $selectedProjectId !== (string) $project->id)>
                            {{ $zoneLabel }}
                        </option>
                    @endforeach
                @endforeach
            </select>
            @if (auth()->user()->hasPermission('projects.update'))
                <button type="button" data-pr-add-zone
                        class="inline-flex h-[38px] shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 text-base font-medium leading-none text-slate-800 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        title="Add zone"
                        @disabled(! $hasProject)>+</button>
            @endif
        </div>
        <p data-pr-zone-hint
           class="mt-1 text-xs leading-snug {{ $selectedZoneLabel !== '' ? 'font-medium text-slate-800' : 'text-slate-500' }}">
            @if ($selectedZoneLabel !== '')
                {{ $selectedZoneLabel }}
            @elseif (! $hasProject)
                Project first
            @endif
        </p>
        @error("items.$index.zone_id")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </td>
    <td class="px-2 py-2 align-top">
        <select name="items[{{ $index }}][category_id]" data-name="category_id" data-pr-category-select required
                class="admin-filter-control w-full min-w-[9rem] text-sm @error("items.$index.category_id") border-red-500 @enderror">
            <option value="">—</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                    {{ $category->name_ar }} — {{ $category->name_en }}
                </option>
            @endforeach
        </select>
        @error("items.$index.category_id")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        @if ($legacyCategory !== '' && ($selectedCategoryId === '' || $selectedCategoryId === null))
            <p class="mt-1 text-xs text-amber-700">Legacy: {{ $legacyCategory }}</p>
        @endif
    </td>
    <td class="px-2 py-2 align-top">
        <div class="flex w-full min-w-[9rem] gap-1">
            <select name="items[{{ $index }}][subcategory_id]" data-name="subcategory_id" data-pr-subcategory-select
                    class="admin-filter-control min-w-0 flex-1 text-sm @error("items.$index.subcategory_id") border-red-500 @enderror"
                    @disabled(! $hasCategory)>
                <option value="">—</option>
                @foreach ($categories as $category)
                    @foreach ($category->subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}"
                                data-category-id="{{ $category->id }}"
                                @selected((string) $selectedSubcategoryId === (string) $subcategory->id)
                                @disabled((string) $selectedCategoryId !== '' && (string) $selectedCategoryId !== (string) $category->id)>
                            {{ $subcategory->name_ar }} — {{ $subcategory->name_en }}
                        </option>
                    @endforeach
                @endforeach
            </select>
            @if ($canQuickAddSubcategory)
                <button type="button" data-pr-add-subcategory
                        class="inline-flex h-[38px] shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 text-base font-medium leading-none text-slate-800 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        title="Add subcategory"
                        @disabled(! $hasCategory)>+</button>
            @endif
        </div>
        @error("items.$index.subcategory_id")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        @if ($legacySubcategory !== '' && ($selectedSubcategoryId === '' || $selectedSubcategoryId === null) && ($selectedCategoryId === '' || $selectedCategoryId === null))
            <p class="mt-1 text-xs text-amber-700">Legacy: {{ $legacySubcategory }}</p>
        @endif
    </td>
    <td class="px-2 py-2 align-top">
        <textarea name="items[{{ $index }}][description]" rows="3" data-name="description" required
                  class="admin-filter-control w-full min-w-[10rem] resize-y @error("items.$index.description") border-red-500 @enderror">{{ old("items.$index.description", $row['description'] ?? '') }}</textarea>
        @error("items.$index.description")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </td>
    <td class="px-2 py-2 align-top">
        <input type="number" name="items[{{ $index }}][quantity]" value="{{ old("items.$index.quantity", $row['quantity'] ?? 1) }}"
               min="0" step="0.001" data-name="quantity" data-pr-boq-qty required
               class="admin-filter-control w-20">
    </td>
    <td class="px-2 py-2 align-top">
        <input type="text" name="items[{{ $index }}][unit]" value="{{ old("items.$index.unit", $row['unit'] ?? '') }}"
               data-name="unit"
               class="admin-filter-control w-16">
    </td>
    <td class="px-2 py-2 align-top">
        <input type="number" name="items[{{ $index }}][unit_price]" value="{{ old("items.$index.unit_price", $row['unit_price'] ?? 0) }}"
               min="0" step="0.0001" data-name="unit_price" data-pr-boq-unit-price
               class="admin-filter-control w-24">
    </td>
    <td class="px-2 py-2 align-top">
        <input type="number" name="items[{{ $index }}][total_price]" value="{{ old("items.$index.total_price", $row['total_price'] ?? 0) }}"
               min="0" step="0.0001" data-name="total_price" data-pr-boq-total readonly
               class="admin-filter-control w-24 bg-slate-50">
    </td>
    <td class="px-2 py-2 align-top print:hidden">
        <button type="button" class="pr-remove-boq-row text-sm font-medium text-red-700 hover:text-red-900">Remove</button>
    </td>
</tr>
