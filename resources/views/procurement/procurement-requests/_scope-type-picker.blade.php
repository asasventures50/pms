@php
    use App\Support\Procurement\ProcurementScopeType;

    $pickerIndex = $pickerIndex ?? 0;
    $selectedScopeTypes = $selectedScopeTypes ?? [];
    $scopeCount = count($selectedScopeTypes);
    if ($scopeCount === 0) {
        $scopeButtonLabel = 'Select scope types';
    } elseif ($scopeCount === 1) {
        $scopeButtonLabel = $selectedScopeTypes[0];
    } elseif ($scopeCount === 2) {
        $scopeButtonLabel = implode(', ', $selectedScopeTypes);
    } else {
        $scopeButtonLabel = $scopeCount . ' selected';
    }
@endphp

<div class="relative mt-1" data-pr-scope-picker>
    <button type="button"
            class="admin-filter-dropdown-btn pr-scope-picker-btn w-full"
            aria-expanded="false"
            aria-haspopup="listbox">
        <span class="pr-scope-picker-label min-w-0 flex-1 truncate text-slate-700">{{ $scopeButtonLabel }}</span>
        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>
    <div class="pr-scope-picker-panel absolute left-0 right-0 top-full z-30 mt-1 hidden overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5"
         role="listbox">
        <div class="max-h-52 overflow-y-auto py-1">
            @foreach (ProcurementScopeType::options() as $scopeType)
                <label class="flex cursor-pointer items-center gap-2.5 px-3 py-2 text-sm hover:bg-slate-50">
                    <input type="checkbox"
                           name="items[{{ $pickerIndex }}][scope_type][]"
                           value="{{ $scopeType }}"
                           data-pr-scope-checkbox
                           data-scope-label="{{ $scopeType }}"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                           @checked(in_array($scopeType, $selectedScopeTypes, true))>
                    <span class="text-slate-800">{{ $scopeType }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
