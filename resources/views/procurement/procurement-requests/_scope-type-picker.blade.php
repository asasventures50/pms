@php
    use App\Support\Procurement\ProcurementScopeType;

    $pickerIndex = $pickerIndex ?? 0;
    $selectedScopeTypes = $selectedScopeTypes ?? [];
@endphp

<div class="mt-1 flex flex-wrap gap-x-5 gap-y-2" data-pr-scope-picker role="group" aria-label="Scope type">
    @foreach (ProcurementScopeType::requestLineOptions() as $scopeType)
        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-800">
            <input type="checkbox"
                   name="items[{{ $pickerIndex }}][scope_type][]"
                   value="{{ $scopeType }}"
                   data-pr-scope-checkbox
                   class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                   @checked(in_array($scopeType, $selectedScopeTypes, true))>
            <span>{{ ProcurementScopeType::label($scopeType) }}</span>
        </label>
    @endforeach
    <p class="w-full text-xs text-slate-500">Optional — select none, one, or more.</p>
</div>
