@php
    $index = $index ?? 0;
    $row = $row ?? [];
    $projects = $projects ?? collect();
    $selectedProjectId = old("items.$index.project_id", $row['project_id'] ?? '');
    $selectedZoneId = old("items.$index.zone_id", $row['zone_id'] ?? '');
    $lineNo = $row['line_number'] ?? null;
    if ($lineNo === null || $lineNo === '') {
        $requestNumber = trim((string) (old('request_number') ?? $procurementRequest?->request_number ?? ($nextCode ?? '')));
        $lineNo = \App\Services\Procurement\ProcurementRequests\ProcurementRequestLineNumberFormatter::format($requestNumber, $index);
    }
@endphp

<article class="pr-line-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
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
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Project</label>
            <select name="items[{{ $index }}][project_id]" data-name="project_id" data-pr-project-select
                    class="admin-filter-control mt-1 w-full">
                <option value="">—</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) $selectedProjectId === (string) $project->id)>
                        {{ $project->code }} — {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Zone</label>
            <select name="items[{{ $index }}][zone_id]" data-name="zone_id" data-pr-zone-select
                    class="admin-filter-control mt-1 w-full">
                <option value="">—</option>
                @foreach ($projects as $project)
                    @foreach ($project->zones as $zone)
                        <option value="{{ $zone->id }}"
                                data-project-id="{{ $project->id }}"
                                @selected((string) $selectedZoneId === (string) $zone->id)>
                            {{ $zone->code }} — {{ $zone->name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Category</label>
            <input type="text" name="items[{{ $index }}][category]" value="{{ $row['category'] ?? '' }}"
                   data-name="category"
                   class="admin-filter-control mt-1 w-full">
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Sub category</label>
            <input type="text" name="items[{{ $index }}][subcategory]" value="{{ $row['subcategory'] ?? '' }}"
                   data-name="subcategory"
                   class="admin-filter-control mt-1 w-full">
        </div>
        <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Scope type</label>
            <input type="text" name="items[{{ $index }}][scope_type]" value="{{ $row['scope_type'] ?? '' }}"
                   data-name="scope_type"
                   class="admin-filter-control mt-1 w-full">
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
</article>
