@php
    $boqItems = old('items', $formDefaults['items'] ?? []);
    $projects = $projects ?? collect();
    $categories = $categories ?? collect();
    $selectedProjectId = old('project_id', $formDefaults['project_id'] ?? '');
    $canQuickAddSubcategory = auth()->user()->hasPermission('categories.create')
        || auth()->user()->hasPermission('procurement-requests.create');
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">BOQ</h3>
            <p class="mt-1 text-xs text-slate-500">Bill of quantities — category & subcategory are per line.</p>
        </div>
        <div class="sm:w-40">
            <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
            <input type="text" name="currency_code" id="currency_code" maxlength="3"
                   value="{{ old('currency_code', $formDefaults['currency_code'] ?? '') }}"
                   class="admin-filter-control mt-1 w-full uppercase @error('currency_code') border-red-500 @enderror"
                   placeholder="USD">
            @error('currency_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    @error('items')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-[72rem] w-full text-left text-sm">
            <colgroup>
                <col class="w-[8%]">
                <col class="w-[14%]">
                <col class="w-[12%]">
                <col class="w-[12%]">
                <col class="w-[22%]">
                <col class="w-[7%]">
                <col class="w-[7%]">
                <col class="w-[8%]">
                <col class="w-[8%]">
                <col class="w-[5%]">
            </colgroup>
            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2">Item</th>
                <th class="px-2 py-2">Zone</th>
                <th class="px-2 py-2">Category <span class="text-red-600">*</span></th>
                <th class="px-2 py-2">Subcategory</th>
                <th class="px-2 py-2">Description <span class="text-red-600">*</span></th>
                <th class="px-2 py-2">Qty</th>
                <th class="px-2 py-2">Unit</th>
                <th class="px-2 py-2">Unit price</th>
                <th class="px-2 py-2">Total</th>
                <th class="px-2 py-2 print:hidden"></th>
            </tr>
            </thead>
            <tbody id="pr-boq-body" class="divide-y divide-slate-100">
            @foreach ($boqItems as $index => $row)
                @include('procurement.procurement-requests._boq-row', [
                    'index' => $index,
                    'row' => $row,
                    'projects' => $projects,
                    'categories' => $categories,
                    'selectedProjectId' => $selectedProjectId,
                    'canQuickAddSubcategory' => $canQuickAddSubcategory,
                ])
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
        @include('procurement.partials._add-line-button', ['id' => 'pr-add-boq-line', 'label' => 'Add BOQ line'])
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Samples required</p>
            <div class="mt-2 flex gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="samples_required" value="1"
                           @checked(old('samples_required', $formDefaults['samples_required'] ?? null) === true || old('samples_required') === '1')
                           class="border-slate-300 text-slate-900 focus:ring-slate-500">
                    <span>Yes</span>
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="samples_required" value="0"
                           @checked(old('samples_required', $formDefaults['samples_required'] ?? null) === false || old('samples_required') === '0')
                           class="border-slate-300 text-slate-900 focus:ring-slate-500">
                    <span>No</span>
                </label>
            </div>
        </div>
    </div>

    <template id="pr-boq-row-template">
        @include('procurement.procurement-requests._boq-row', [
            'index' => 0,
            'row' => [
                'item_name' => '',
                'zone_id' => '',
                'category_id' => '',
                'subcategory_id' => '',
                'description' => '',
                'unit' => '',
                'quantity' => 1,
                'unit_price' => 0,
                'total_price' => 0,
            ],
            'projects' => $projects,
            'categories' => $categories,
            'selectedProjectId' => $selectedProjectId,
            'canQuickAddSubcategory' => $canQuickAddSubcategory,
        ])
    </template>
</section>

@if (auth()->user()->hasPermission('projects.update'))
    @include('procurement.procurement-requests.partials._quick-add-zone-modal')
@endif
@if ($canQuickAddSubcategory)
    @include('procurement.procurement-requests.partials._quick-add-subcategory-modal')
@endif
