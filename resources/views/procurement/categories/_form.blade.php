@php
    $c = $category;
    $oldSubs = old('subcategories');
    if (is_array($oldSubs)) {
        $subRows = array_values($oldSubs);
    } elseif ($mode === 'edit') {
        $subRows = $c->subcategories->map(fn ($s) => [
            'id' => $s->id,
            'name_ar' => $s->name_ar,
            'name_en' => $s->name_en,
            'slug' => $s->slug,
            'status' => $s->status,
        ])->values()->all();
    } else {
        $subRows = [['name_ar' => '', 'name_en' => '', 'slug' => '', 'status' => 'active']];
    }
    if (count($subRows) === 0) {
        $subRows = [['name_ar' => '', 'name_en' => '', 'slug' => '', 'status' => 'active']];
    }
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Category</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="name_ar" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Arabic name <span class="text-red-600">*</span></label>
            <input type="text" name="name_ar" id="name_ar" required value="{{ old('name_ar', $c->name_ar ?? '') }}" dir="auto"
                   class="admin-filter-control @error('name_ar') border-red-500 @enderror">
            @error('name_ar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="name_en" class="block text-xs font-medium uppercase tracking-wide text-slate-500">English name <span class="text-red-600">*</span></label>
            <input type="text" name="name_en" id="category_name_en" required value="{{ old('name_en', $c->name_en ?? '') }}"
                   data-slug-source
                   class="admin-filter-control @error('name_en') border-red-500 @enderror">
            @error('name_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="slug" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Slug <span class="text-red-600">*</span></label>
            <input type="text" name="slug" id="category_slug" required value="{{ old('slug', $c->slug ?? '') }}"
                   data-slug-target data-slug-manual="0"
                   class="admin-filter-control font-mono @error('slug') border-red-500 @enderror">
            <p class="mt-1 text-xs text-slate-500">Generated from English name; you can edit manually.</p>
            @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status <span class="text-red-600">*</span></label>
            <select name="status" id="status" required
                    class="admin-filter-control @error('status') border-red-500 @enderror">
                @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $c->status ?? 'active') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-base font-semibold text-slate-900">Subcategories</h2>
        <button type="button" id="add-subcategory-row"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
            Add subcategory row
        </button>
    </div>
    <p class="mt-2 text-xs text-slate-500">Arabic name is shown first. Slugs are generated from English name unless you edit them.</p>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2 text-left">Arabic name</th>
                <th class="px-2 py-2 text-left">English name</th>
                <th class="px-2 py-2 text-left">Slug</th>
                <th class="px-2 py-2 text-left">Status</th>
                <th class="px-2 py-2 text-left w-24"></th>
            </tr>
            </thead>
            <tbody id="subcategory-rows" class="divide-y divide-slate-100">
            @foreach ($subRows as $index => $row)
                <tr class="subcategory-row" data-row-index="{{ $index }}">
                    @if ($mode === 'edit' && ! empty($row['id']))
                        <input type="hidden" name="subcategories[{{ $index }}][id]" value="{{ $row['id'] }}">
                    @endif
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="subcategories[{{ $index }}][name_ar]" value="{{ $row['name_ar'] ?? '' }}" dir="auto"
                               class="admin-filter-control !mt-0 min-w-[8rem] @error('subcategories.'.$index.'.name_ar') border-red-500 @enderror">
                        @error('subcategories.'.$index.'.name_ar')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="subcategories[{{ $index }}][name_en]" value="{{ $row['name_en'] ?? '' }}" data-sub-slug-source
                               class="admin-filter-control !mt-0 min-w-[8rem] @error('subcategories.'.$index.'.name_en') border-red-500 @enderror">
                        @error('subcategories.'.$index.'.name_en')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </td>
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="subcategories[{{ $index }}][slug]" value="{{ $row['slug'] ?? '' }}" data-sub-slug-target data-slug-manual="0"
                               class="admin-filter-control !mt-0 min-w-[8rem] font-mono text-xs @error('subcategories.'.$index.'.slug') border-red-500 @enderror">
                        @error('subcategories.'.$index.'.slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </td>
                    <td class="px-2 py-2 align-top">
                        <select name="subcategories[{{ $index }}][status]"
                                class="admin-filter-control !mt-0 @error('subcategories.'.$index.'.status') border-red-500 @enderror">
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                                <option value="{{ $val }}" @selected(($row['status'] ?? 'active') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('subcategories.'.$index.'.status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </td>
                    <td class="px-2 py-2 align-top">
                        <button type="button" class="remove-subcategory-row text-sm font-medium text-red-700 hover:text-red-900">Remove</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @error('subcategories')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</section>

<template id="subcategory-row-template">
    <tr class="subcategory-row" data-row-index="__IDX__">
        <td class="px-2 py-2 align-top">
            <input type="text" name="subcategories[__IDX__][name_ar]" value="" dir="auto"
                   class="admin-filter-control !mt-0 min-w-[8rem]">
        </td>
        <td class="px-2 py-2 align-top">
            <input type="text" name="subcategories[__IDX__][name_en]" value="" data-sub-slug-source
                   class="admin-filter-control !mt-0 min-w-[8rem]">
        </td>
        <td class="px-2 py-2 align-top">
            <input type="text" name="subcategories[__IDX__][slug]" value="" data-sub-slug-target data-slug-manual="0"
                   class="admin-filter-control !mt-0 min-w-[8rem] font-mono text-xs">
        </td>
        <td class="px-2 py-2 align-top">
            <select name="subcategories[__IDX__][status]" class="admin-filter-control !mt-0">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </td>
        <td class="px-2 py-2 align-top">
            <button type="button" class="remove-subcategory-row text-sm font-medium text-red-700 hover:text-red-900">Remove</button>
        </td>
    </tr>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function slugify(text) {
                return text
                    .toString()
                    .normalize('NFKD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            function wireCategorySlug() {
                const src = document.querySelector('[data-slug-source]');
                const tgt = document.querySelector('[data-slug-target]');
                if (!src || !tgt) {
                    return;
                }
                src.addEventListener('input', function () {
                    if (tgt.dataset.slugManual === '1') {
                        return;
                    }
                    tgt.value = slugify(src.value);
                });
                tgt.addEventListener('input', function () {
                    tgt.dataset.slugManual = tgt.value === '' ? '0' : '1';
                });
            }

            function wireSubRow(row) {
                const src = row.querySelector('[data-sub-slug-source]');
                const tgt = row.querySelector('[data-sub-slug-target]');
                if (!src || !tgt) {
                    return;
                }
                src.addEventListener('input', function () {
                    if (tgt.dataset.slugManual === '1') {
                        return;
                    }
                    tgt.value = slugify(src.value);
                });
                tgt.addEventListener('input', function () {
                    tgt.dataset.slugManual = tgt.value === '' ? '0' : '1';
                });
            }

            document.querySelectorAll('#subcategory-rows tr.subcategory-row').forEach(wireSubRow);

            const tbody = document.getElementById('subcategory-rows');
            const tpl = document.getElementById('subcategory-row-template');
            const addBtn = document.getElementById('add-subcategory-row');

            function nextIndex() {
                const rows = tbody.querySelectorAll('tr.subcategory-row');
                let max = -1;
                rows.forEach(function (row) {
                    const idx = parseInt(row.getAttribute('data-row-index'), 10);
                    if (!isNaN(idx) && idx > max) {
                        max = idx;
                    }
                });
                return max + 1;
            }

            if (addBtn && tpl && tbody) {
                addBtn.addEventListener('click', function () {
                    const idx = nextIndex();
                    const html = tpl.innerHTML.replaceAll('__IDX__', String(idx));
                    tbody.insertAdjacentHTML('beforeend', html);
                    const row = tbody.lastElementChild;
                    row.dataset.rowIndex = String(idx);
                    wireSubRow(row);
                    row.querySelector('.remove-subcategory-row').addEventListener('click', function () {
                        row.remove();
                    });
                });
            }

            tbody.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-subcategory-row')) {
                    const row = e.target.closest('tr');
                    if (row) {
                        row.remove();
                    }
                }
            });

            wireCategorySlug();
        });
    </script>
@endpush
