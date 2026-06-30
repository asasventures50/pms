@php
    use App\Enums\Procurement\ProcurementRequests\GeographicScope;
    use App\Enums\Procurement\ProcurementRequests\ProcurementType;
    use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;

    $selectedProjectId = old('project_id', $formDefaults['project_id'] ?? '');
    $selectedCategoryId = old('category_id', $formDefaults['category_id'] ?? '');
    $selectedSubcategoryId = old('subcategory_id', $formDefaults['subcategory_id'] ?? '');
    $selectedProcurementTypes = old('procurement_types', $formDefaults['procurement_types'] ?? []);
    $selectedGeographicScopes = old('geographic_scopes', $formDefaults['geographic_scopes'] ?? []);
    $selectedVendorTypes = old('vendor_types', $formDefaults['vendor_types'] ?? []);
    $canQuickAddSubcategory = auth()->user()->hasPermission('categories.create')
        || auth()->user()->hasPermission('procurement-requests.create');
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-900">PR information</h3>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                Project <span class="normal-case text-red-600">*</span>
            </label>
            <div class="mt-1 flex gap-1">
                <select name="project_id" id="pr_project_id" data-pr-project-select required
                        class="admin-filter-control min-w-0 flex-1 @error('project_id') border-red-500 @enderror">
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
                            title="Add project">+</button>
                @endif
            </div>
            @error('project_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                Category <span class="normal-case text-red-600">*</span>
            </label>
            <select name="category_id" id="pr_category_id" data-pr-category-select required
                    class="admin-filter-control mt-1 w-full @error('category_id') border-red-500 @enderror">
                <option value="">—</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                        {{ $category->name_ar }} — {{ $category->name_en }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @if (! empty($formDefaults['legacy_category']) && empty($selectedCategoryId))
                <p class="mt-1 text-xs text-amber-700">Legacy value: {{ $formDefaults['legacy_category'] }}</p>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Subcategory</label>
            <div class="mt-1 flex gap-1">
                <select name="subcategory_id" id="pr_subcategory_id" data-pr-subcategory-select
                        class="admin-filter-control min-w-0 flex-1 @error('subcategory_id') border-red-500 @enderror"
                        @disabled($selectedCategoryId === '' || $selectedCategoryId === null)>
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
                            class="inline-flex shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white px-2.5 text-base font-medium leading-none text-slate-800 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            title="Add subcategory"
                            @disabled($selectedCategoryId === '' || $selectedCategoryId === null)>+</button>
                @endif
            </div>
            @error('subcategory_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @include('procurement.procurement-requests._pr-checkbox-group', [
            'name' => 'procurement_types',
            'label' => 'Procurement type',
            'options' => ProcurementType::cases(),
            'selected' => $selectedProcurementTypes,
        ])
        @include('procurement.procurement-requests._pr-checkbox-group', [
            'name' => 'geographic_scopes',
            'label' => 'Local / International',
            'options' => GeographicScope::cases(),
            'selected' => $selectedGeographicScopes,
            'required' => true,
            'hint' => 'Select both for Local & International.',
        ])
        @include('procurement.procurement-requests._pr-checkbox-group', [
            'name' => 'vendor_types',
            'label' => 'Vendor type',
            'options' => ProcurementVendorType::cases(),
            'selected' => $selectedVendorTypes,
        ])
    </div>
</section>

@if (auth()->user()->hasPermission('projects.create'))
    @include('procurement.procurement-requests.partials._quick-add-project-modal')
@endif
@if ($canQuickAddSubcategory)
    @include('procurement.procurement-requests.partials._quick-add-subcategory-modal')
@endif
