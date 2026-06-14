@php
    $c = $category;
    $currentCategoryId = (int) ($c->id ?? 0);
    $categoryOptions = ($mode === 'edit' && isset($allCategories))
        ? $allCategories->sortBy(fn ($category) => mb_strtolower($category->name_ar ?? ''))->values()
        : collect();
    $categoryPickerOptions = $categoryOptions->map(fn ($category) => [
        'id' => (string) $category->id,
        'label' => trim($category->name_ar.' — '.$category->name_en, ' —'),
        'search' => mb_strtolower(trim($category->name_ar.' '.$category->name_en)),
    ])->values();
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
            'target_category_id' => $currentCategoryId,
        ])->values()->all();
    } else {
        $subRows = [['name_ar' => '', 'name_en' => '', 'slug' => '', 'status' => 'active']];
    }
    if (count($subRows) === 0) {
        $subRows = $mode === 'edit'
            ? []
            : [['name_ar' => '', 'name_en' => '', 'slug' => '', 'status' => 'active']];
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
    @if ($mode === 'edit')
        <p class="mt-2 text-xs text-slate-500">Change the parent category to move a subcategory. Vendor links, brochures, and procurement requests will be updated on save.</p>
    @else
        <p class="mt-2 text-xs text-slate-500">Arabic name is shown first. Slugs are generated from English name unless you edit them.</p>
    @endif

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2 text-left">Arabic name</th>
                <th class="px-2 py-2 text-left">English name</th>
                <th class="px-2 py-2 text-left">Slug</th>
                @if ($mode === 'edit')
                    <th class="px-2 py-2 text-left">Parent category</th>
                @endif
                <th class="px-2 py-2 text-left">Status</th>
                <th class="px-2 py-2 text-left w-24"></th>
            </tr>
            </thead>
            <tbody id="subcategory-rows" class="divide-y divide-slate-100">
            @foreach ($subRows as $index => $row)
                @php
                    $selectedParentId = (int) old("subcategories.$index.target_category_id", $row['target_category_id'] ?? $currentCategoryId);
                @endphp
                <tr class="subcategory-row" data-row-index="{{ $index }}" @if ($mode === 'edit') data-subcategory-id="{{ $row['id'] ?? '' }}" data-current-category-id="{{ $currentCategoryId }}" @endif>
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
                    @if ($mode === 'edit')
                        <td class="px-2 py-2 align-top">
                            @include('partials.searchable-select', [
                                'name' => 'subcategories['.$index.'][target_category_id]',
                                'selectedValue' => $selectedParentId,
                                'options' => $categoryOptions,
                                'searchPlaceholder' => 'Search categories…',
                                'inputAttributes' => ['data-target-category-select' => '1'],
                            ])
                            <p class="mt-1 hidden text-xs font-medium text-amber-700" data-move-warning>Will move on save</p>
                            @error('subcategories.'.$index.'.target_category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                    @endif
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
    <tr class="subcategory-row" data-row-index="__IDX__" @if ($mode === 'edit') data-current-category-id="{{ $currentCategoryId }}" @endif>
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
        @if ($mode === 'edit')
            <td class="px-2 py-2 align-top">
                <div data-searchable-select-placeholder
                     data-name="subcategories[__IDX__][target_category_id]"
                     data-selected="{{ $currentCategoryId }}"></div>
                <p class="mt-1 hidden text-xs font-medium text-amber-700" data-move-warning>Will move on save</p>
            </td>
        @endif
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
            const isEditMode = @json($mode === 'edit');
            const currentCategoryId = @json($currentCategoryId);
            const movePreviewBaseUrl = @json($mode === 'edit' ? url('/categories/subcategories') : null);
            const categoryPickerOptions = @json($mode === 'edit' ? $categoryPickerOptions : []);

            let openSearchableSelect = null;

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

            function escapeHtml(text) {
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function findSelectedOptionLabel(selectedValue) {
                const match = categoryPickerOptions.find(function (option) {
                    return String(option.id) === String(selectedValue);
                });

                return match ? match.label : 'Select…';
            }

            function buildSearchableSelectMarkup(name, selectedValue) {
                const selectedLabel = findSelectedOptionLabel(selectedValue);
                const optionsHtml = categoryPickerOptions.map(function (option) {
                    const isSelected = String(option.id) === String(selectedValue);
                    return '<button type="button" role="option" data-searchable-select-option'
                        + ' data-value="' + escapeHtml(option.id) + '"'
                        + ' data-label="' + escapeHtml(option.label) + '"'
                        + ' data-search="' + escapeHtml(option.search) + '"'
                        + ' class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50'
                        + (isSelected ? ' bg-slate-100 font-medium text-slate-900' : '')
                        + '"><span dir="auto">' + escapeHtml(option.label) + '</span></button>';
                }).join('');

                return ''
                    + '<div class="searchable-select relative min-w-[12rem]" data-searchable-select>'
                    + '<input type="hidden" name="' + escapeHtml(name) + '" value="' + escapeHtml(selectedValue) + '" data-searchable-select-value data-target-category-select>'
                    + '<button type="button" class="admin-filter-dropdown-btn !mt-0 w-full" data-searchable-select-btn aria-haspopup="listbox" aria-expanded="false">'
                    + '<span class="min-w-0 flex-1 truncate text-left" data-searchable-select-label">' + escapeHtml(selectedLabel) + '</span>'
                    + '<svg class="h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">'
                    + '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>'
                    + '</svg></button>'
                    + '<div class="fixed z-[9999] hidden overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5" data-searchable-select-panel role="listbox">'
                    + '<div class="border-b border-slate-100 p-2">'
                    + '<input type="search" autocomplete="off" placeholder="Search categories…" class="admin-filter-control !mt-0 w-full" data-searchable-select-search>'
                    + '</div>'
                    + '<div class="max-h-52 overflow-y-auto overscroll-y-contain py-1" data-searchable-select-list">' + optionsHtml + '</div>'
                    + '<p class="hidden px-3 py-2 text-sm text-slate-500" data-searchable-select-empty>No matches found.</p>'
                    + '</div></div>';
            }

            function getSearchableSelectRoot(panel) {
                const rootId = panel.dataset.searchableSelectRootId;
                if (rootId) {
                    return document.querySelector('[data-searchable-select-root-id="' + rootId + '"]');
                }

                return panel.closest('[data-searchable-select]');
            }

            function positionSearchableSelectPanel(btn, panel) {
                const rect = btn.getBoundingClientRect();
                const width = Math.max(rect.width, 192);
                let left = rect.left;
                const margin = 8;

                if (left + width > window.innerWidth - margin) {
                    left = window.innerWidth - width - margin;
                }
                if (left < margin) {
                    left = margin;
                }

                panel.style.width = width + 'px';
                panel.style.left = left + 'px';

                panel.classList.remove('hidden');
                const panelHeight = panel.offsetHeight || 280;
                const spaceBelow = window.innerHeight - rect.bottom - margin;
                const spaceAbove = rect.top - margin;

                if (spaceBelow >= panelHeight || spaceBelow >= spaceAbove) {
                    panel.style.top = (rect.bottom + 4) + 'px';
                } else {
                    panel.style.top = Math.max(margin, rect.top - panelHeight - 4) + 'px';
                }
            }

            function attachSearchableSelectPanel(root, panel) {
                if (panel.parentElement !== document.body) {
                    document.body.appendChild(panel);
                }
                panel.dataset.searchableSelectDetached = '1';
            }

            function restoreSearchableSelectPanel(root, panel) {
                panel.classList.add('hidden');
                panel.style.top = '';
                panel.style.left = '';
                panel.style.width = '';
                panel.dataset.searchableSelectDetached = '0';

                if (root && panel.parentElement === document.body) {
                    root.appendChild(panel);
                }
            }

            function closeSearchableSelectPanel(panel) {
                if (!panel) {
                    return;
                }

                const root = getSearchableSelectRoot(panel);
                restoreSearchableSelectPanel(root, panel);

                const btn = root ? root.querySelector('[data-searchable-select-btn]') : null;
                if (btn) {
                    btn.setAttribute('aria-expanded', 'false');
                }

                if (openSearchableSelect && openSearchableSelect.panel === panel) {
                    openSearchableSelect = null;
                }
            }

            function closeAllSearchableSelectPanels(exceptPanel) {
                document.querySelectorAll('[data-searchable-select-panel]').forEach(function (panel) {
                    if (panel !== exceptPanel) {
                        closeSearchableSelectPanel(panel);
                    }
                });
            }

            function filterSearchableSelectOptions(panel, query) {
                const normalized = query.trim().toLowerCase();
                let visibleCount = 0;

                panel.querySelectorAll('[data-searchable-select-option]').forEach(function (option) {
                    const haystack = (option.getAttribute('data-search') || option.textContent || '').toLowerCase();
                    const visible = normalized === '' || haystack.includes(normalized);
                    option.classList.toggle('hidden', !visible);
                    if (visible) {
                        visibleCount++;
                    }
                });

                const emptyState = panel.querySelector('[data-searchable-select-empty]');
                if (emptyState) {
                    emptyState.classList.toggle('hidden', visibleCount > 0);
                }
            }

            function setSearchableSelectValue(root, value, label) {
                const hidden = root.querySelector('[data-searchable-select-value]');
                const labelEl = root.querySelector('[data-searchable-select-label]');
                if (!hidden || !labelEl) {
                    return;
                }

                hidden.value = value;
                labelEl.textContent = label;
                hidden.dispatchEvent(new Event('change', { bubbles: true }));

                root.querySelectorAll('[data-searchable-select-option]').forEach(function (option) {
                    const isSelected = option.getAttribute('data-value') === String(value);
                    option.classList.toggle('bg-slate-100', isSelected);
                    option.classList.toggle('font-medium', isSelected);
                    option.classList.toggle('text-slate-900', isSelected);
                });

                const panel = root.querySelector('[data-searchable-select-panel]');
                if (panel) {
                    panel.querySelectorAll('[data-searchable-select-option]').forEach(function (option) {
                        const isSelected = option.getAttribute('data-value') === String(value);
                        option.classList.toggle('bg-slate-100', isSelected);
                        option.classList.toggle('font-medium', isSelected);
                        option.classList.toggle('text-slate-900', isSelected);
                    });
                }
            }

            function openSearchableSelectPanel(root, btn, panel, searchInput) {
                attachSearchableSelectPanel(root, panel);
                positionSearchableSelectPanel(btn, panel);
                btn.setAttribute('aria-expanded', 'true');
                openSearchableSelect = { root: root, btn: btn, panel: panel };

                if (searchInput) {
                    searchInput.value = '';
                    filterSearchableSelectOptions(panel, '');
                    searchInput.focus();
                }
            }

            function initSearchableSelect(root) {
                if (!root || root.dataset.searchableSelectWired === '1') {
                    return;
                }

                root.dataset.searchableSelectWired = '1';
                root.dataset.searchableSelectRootId = 'searchable-select-' + Math.random().toString(36).slice(2);

                const btn = root.querySelector('[data-searchable-select-btn]');
                const panel = root.querySelector('[data-searchable-select-panel]');
                const searchInput = panel ? panel.querySelector('[data-searchable-select-search]') : null;

                if (!btn || !panel) {
                    return;
                }

                panel.dataset.searchableSelectRootId = root.dataset.searchableSelectRootId;

                btn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const isOpen = openSearchableSelect && openSearchableSelect.panel === panel;
                    closeAllSearchableSelectPanels(isOpen ? null : panel);

                    if (isOpen) {
                        closeSearchableSelectPanel(panel);
                    } else {
                        openSearchableSelectPanel(root, btn, panel, searchInput);
                    }
                });

                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        filterSearchableSelectOptions(panel, searchInput.value);
                    });

                    searchInput.addEventListener('keydown', function (event) {
                        event.stopPropagation();
                    });
                }

                panel.querySelectorAll('[data-searchable-select-option]').forEach(function (option) {
                    option.addEventListener('click', function () {
                        setSearchableSelectValue(
                            root,
                            option.getAttribute('data-value') || '',
                            option.getAttribute('data-label') || option.textContent.trim()
                        );
                        closeSearchableSelectPanel(panel);
                    });
                });
            }

            function mountSearchableSelectPlaceholders(scope) {
                scope.querySelectorAll('[data-searchable-select-placeholder]').forEach(function (placeholder) {
                    const markup = buildSearchableSelectMarkup(
                        placeholder.getAttribute('data-name') || '',
                        placeholder.getAttribute('data-selected') || ''
                    );
                    placeholder.outerHTML = markup;
                });
            }

            function initSearchableSelects(scope) {
                mountSearchableSelectPlaceholders(scope);
                scope.querySelectorAll('[data-searchable-select]').forEach(initSearchableSelect);
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

            function getTargetCategoryValue(row) {
                const input = row.querySelector('[data-target-category-select]');
                return input ? parseInt(input.value, 10) : NaN;
            }

            function updateMoveWarning(row) {
                if (!isEditMode) {
                    return;
                }

                const hiddenInput = row.querySelector('[data-target-category-select]');
                const warning = row.querySelector('[data-move-warning]');
                if (!hiddenInput || !warning) {
                    return;
                }

                const subcategoryId = row.dataset.subcategoryId || '';
                const targetId = parseInt(hiddenInput.value, 10);
                const isMove = subcategoryId !== '' && !isNaN(targetId) && targetId !== currentCategoryId;
                warning.classList.toggle('hidden', !isMove);
            }

            function wireTargetCategorySelect(row) {
                if (!isEditMode) {
                    return;
                }

                const hiddenInput = row.querySelector('[data-target-category-select]');
                if (!hiddenInput) {
                    return;
                }

                hiddenInput.addEventListener('change', function () {
                    updateMoveWarning(row);
                });
                updateMoveWarning(row);
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
                wireTargetCategorySelect(row);
            }

            initSearchableSelects(document);
            document.querySelectorAll('#subcategory-rows tr.subcategory-row').forEach(wireSubRow);

            document.addEventListener('click', function (event) {
                if (event.target.closest('[data-searchable-select]') || event.target.closest('[data-searchable-select-panel]')) {
                    return;
                }
                closeAllSearchableSelectPanels();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeAllSearchableSelectPanels();
                }
            });

            window.addEventListener('resize', function () {
                if (!openSearchableSelect) {
                    return;
                }
                positionSearchableSelectPanel(openSearchableSelect.btn, openSearchableSelect.panel);
            });

            window.addEventListener('scroll', function (event) {
                if (!openSearchableSelect) {
                    return;
                }

                const panel = openSearchableSelect.panel;
                const scrollTarget = event.target;

                if (scrollTarget instanceof Node && (scrollTarget === panel || panel.contains(scrollTarget))) {
                    return;
                }

                positionSearchableSelectPanel(openSearchableSelect.btn, panel);
            }, true);

            const tbody = document.getElementById('subcategory-rows');
            const tpl = document.getElementById('subcategory-row-template');
            const addBtn = document.getElementById('add-subcategory-row');
            const form = document.querySelector('form[action*="categories"]');

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

            function rowsMarkedForMove() {
                return Array.from(tbody.querySelectorAll('tr.subcategory-row')).filter(function (row) {
                    const subcategoryId = row.dataset.subcategoryId || '';
                    if (subcategoryId === '') {
                        return false;
                    }
                    const targetId = getTargetCategoryValue(row);
                    return !isNaN(targetId) && targetId !== currentCategoryId;
                });
            }

            async function fetchMovePreview(subcategoryId, targetCategoryId) {
                const url = movePreviewBaseUrl + '/' + subcategoryId + '/move-preview?target_category_id=' + encodeURIComponent(targetCategoryId);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Preview request failed');
                }

                return response.json();
            }

            async function buildMoveConfirmMessage(rows) {
                let vendorLinks = 0;
                let brochures = 0;
                let procurementRequests = 0;
                let conflicts = [];

                for (const row of rows) {
                    const subcategoryId = row.dataset.subcategoryId;
                    const targetCategoryId = row.querySelector('[data-target-category-select]')?.value;
                    const nameEn = row.querySelector('[data-sub-slug-source]')?.value || 'Subcategory';

                    try {
                        const preview = await fetchMovePreview(subcategoryId, targetCategoryId);
                        vendorLinks += preview.vendor_links || 0;
                        brochures += preview.brochures || 0;
                        procurementRequests += preview.procurement_requests || 0;

                        if (preview.has_name_conflict || preview.has_slug_conflict) {
                            conflicts.push(nameEn);
                        }
                    } catch (error) {
                        conflicts.push(nameEn);
                    }
                }

                if (conflicts.length > 0) {
                    return 'Unable to preview one or more subcategory moves. Please review the form and try again.';
                }

                return 'Moving ' + rows.length + ' subcategory row(s) will update '
                    + vendorLinks + ' vendor link(s), '
                    + brochures + ' brochure(s), and '
                    + procurementRequests + ' procurement request(s) to reflect the new parent categories.\n\nContinue?';
            }

            if (addBtn && tpl && tbody) {
                addBtn.addEventListener('click', function () {
                    const idx = nextIndex();
                    const html = tpl.innerHTML.replaceAll('__IDX__', String(idx));
                    tbody.insertAdjacentHTML('beforeend', html);
                    const row = tbody.lastElementChild;
                    row.dataset.rowIndex = String(idx);
                    initSearchableSelects(row);
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

            if (form && isEditMode) {
                form.addEventListener('submit', async function (event) {
                    const rows = rowsMarkedForMove();
                    if (rows.length === 0) {
                        return;
                    }

                    event.preventDefault();
                    const message = await buildMoveConfirmMessage(rows);
                    if (window.confirm(message)) {
                        form.submit();
                    }
                });
            }

            wireCategorySlug();
        });
    </script>
@endpush
