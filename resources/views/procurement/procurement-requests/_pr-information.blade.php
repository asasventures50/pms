@php
    use App\Enums\Procurement\ProcurementRequests\GeographicScope;
    use App\Enums\Procurement\ProcurementRequests\ProcurementType;
    use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;

    $selectedProjectId = old('project_id', $formDefaults['project_id'] ?? '');
    $selectedProcurementTypes = old('procurement_types', $formDefaults['procurement_types'] ?? []);
    $selectedGeographicScopes = old('geographic_scopes', $formDefaults['geographic_scopes'] ?? []);
    $selectedVendorTypes = old('vendor_types', $formDefaults['vendor_types'] ?? []);
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
        <div>
            <label for="pr_package" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                Package <span class="font-normal normal-case text-slate-400">/ الحزمة</span>
            </label>
            <input type="text" name="package" id="pr_package" maxlength="500"
                   value="{{ old('package', $formDefaults['package'] ?? '') }}"
                   class="admin-filter-control mt-1 w-full @error('package') border-red-500 @enderror"
                   placeholder="Optional">
            @error('package')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
