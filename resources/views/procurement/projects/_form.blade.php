@php
    $p = $project;
    $oldZones = old('zones');
    if (is_array($oldZones)) {
        $zoneRows = array_values($oldZones);
    } elseif ($mode === 'edit') {
        $zoneRows = $p->zones->map(fn ($z) => [
            'id' => $z->id,
            'code' => $z->code,
            'name' => $z->name,
            'status' => $z->status,
        ])->values()->all();
    } else {
        $zoneRows = [['name' => '', 'status' => 'active']];
    }
    if (count($zoneRows) === 0) {
        $zoneRows = [['name' => '', 'status' => 'active']];
    }
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Project</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <p class="block text-xs font-medium uppercase tracking-wide text-slate-500">Project code</p>
            @if ($mode === 'create')
                <p class="mt-1 font-mono text-sm text-slate-900">{{ $nextProjectCode ?? '—' }}</p>
                <p class="mt-1 text-xs text-slate-500">Assigned automatically when you save (e.g. PRJ-0001).</p>
            @else
                <p class="mt-1 font-mono text-sm text-slate-900">{{ $p->code }}</p>
            @endif
        </div>
        <div>
            <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Name <span class="text-red-600">*</span></label>
            <input type="text" name="name" id="name" required value="{{ old('name', $p->name ?? '') }}"
                   class="admin-filter-control @error('name') border-red-500 @enderror">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status <span class="text-red-600">*</span></label>
            <select name="status" id="status" required
                    class="admin-filter-control @error('status') border-red-500 @enderror">
                @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $p->status ?? 'active') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-2 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Zones</h2>
            <p class="mt-1 text-xs text-slate-500">Enter a name per zone. Codes (e.g. Z-0001) are generated automatically per project.</p>
        </div>
        <button type="button" id="add-zone-row"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
            Add zone row
        </button>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                @if ($mode === 'edit')
                    <th class="px-2 py-2 text-left">Code</th>
                @endif
                <th class="px-2 py-2 text-left">Name</th>
                <th class="px-2 py-2 text-left">Status</th>
                <th class="px-2 py-2 text-left w-24"></th>
            </tr>
            </thead>
            <tbody id="zone-rows" class="divide-y divide-slate-100">
            @foreach ($zoneRows as $index => $row)
                <tr class="zone-row" data-row-index="{{ $index }}">
                    @if ($mode === 'edit' && ! empty($row['id']))
                        <input type="hidden" name="zones[{{ $index }}][id]" value="{{ $row['id'] }}">
                    @endif
                    @if ($mode === 'edit')
                        <td class="px-2 py-2 align-top font-mono text-xs text-slate-600">
                            {{ ! empty($row['id']) ? ($row['code'] ?? '—') : 'Auto' }}
                        </td>
                    @endif
                    <td class="px-2 py-2 align-top">
                        <input type="text" name="zones[{{ $index }}][name]" value="{{ $row['name'] ?? '' }}"
                               class="admin-filter-control !mt-0 min-w-[10rem] @error('zones.'.$index.'.name') border-red-500 @enderror">
                        @error('zones.'.$index.'.name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </td>
                    <td class="px-2 py-2 align-top">
                        <select name="zones[{{ $index }}][status]"
                                class="admin-filter-control !mt-0 @error('zones.'.$index.'.status') border-red-500 @enderror">
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $val => $label)
                                <option value="{{ $val }}" @selected(($row['status'] ?? 'active') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('zones.'.$index.'.status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </td>
                    <td class="px-2 py-2 align-top">
                        <button type="button" class="remove-zone-row text-sm font-medium text-red-700 hover:text-red-900">Remove</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @error('zones')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</section>

<template id="zone-row-template">
    <tr class="zone-row" data-row-index="__IDX__">
        @if ($mode === 'edit')
            <td class="px-2 py-2 align-top font-mono text-xs text-slate-500">Auto</td>
        @endif
        <td class="px-2 py-2 align-top">
            <input type="text" name="zones[__IDX__][name]" value=""
                   class="admin-filter-control !mt-0 min-w-[10rem]">
        </td>
        <td class="px-2 py-2 align-top">
            <select name="zones[__IDX__][status]" class="admin-filter-control !mt-0">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </td>
        <td class="px-2 py-2 align-top">
            <button type="button" class="remove-zone-row text-sm font-medium text-red-700 hover:text-red-900">Remove</button>
        </td>
    </tr>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tbody = document.getElementById('zone-rows');
            const tpl = document.getElementById('zone-row-template');
            const addBtn = document.getElementById('add-zone-row');

            function nextIndex() {
                const rows = tbody.querySelectorAll('tr.zone-row');
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
                    row.querySelector('.remove-zone-row').addEventListener('click', function () {
                        row.remove();
                    });
                });
            }

            tbody.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-zone-row')) {
                    const row = e.target.closest('tr');
                    if (row && tbody.querySelectorAll('tr.zone-row').length > 1) {
                        row.remove();
                    }
                }
            });
        });
    </script>
@endpush
