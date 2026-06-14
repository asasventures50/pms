@php
    /** @var iterable<int, object{id: int|string, name_en?: string, name_ar?: string, label?: string, search?: string}> $options */
    $selectedValue = (string) ($selectedValue ?? '');
    $placeholder = $placeholder ?? 'Select…';
    $searchPlaceholder = $searchPlaceholder ?? 'Search…';
    $selectedLabel = $placeholder;

    foreach ($options as $option) {
        if ((string) ($option->id ?? '') === $selectedValue) {
            $selectedLabel = $option->label ?? trim(($option->name_ar ?? '').' — '.($option->name_en ?? ''), ' —');
            break;
        }
    }
@endphp

<div class="searchable-select relative min-w-[12rem]" data-searchable-select>
    <input type="hidden"
           name="{{ $name }}"
           value="{{ $selectedValue }}"
           @if (! empty($inputAttributes))
               @foreach ($inputAttributes as $attr => $val)
                   {{ $attr }}="{{ $val }}"
               @endforeach
           @endif
           data-searchable-select-value>
    <button type="button"
            class="admin-filter-dropdown-btn !mt-0 w-full"
            data-searchable-select-btn
            aria-haspopup="listbox"
            aria-expanded="false">
        <span class="min-w-0 flex-1 truncate text-left" data-searchable-select-label>{{ $selectedLabel }}</span>
        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>
    <div class="fixed z-[9999] hidden overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5"
         data-searchable-select-panel
         role="listbox">
        <div class="border-b border-slate-100 p-2">
            <input type="search"
                   autocomplete="off"
                   placeholder="{{ $searchPlaceholder }}"
                   class="admin-filter-control !mt-0 w-full"
                   data-searchable-select-search>
        </div>
        <div class="max-h-52 overflow-y-auto overscroll-y-contain py-1" data-searchable-select-list>
            @foreach ($options as $option)
                @php
                    $optionId = (string) ($option->id ?? '');
                    $optionLabel = $option->label ?? trim(($option->name_ar ?? '').' — '.($option->name_en ?? ''), ' —');
                    $optionSearch = $option->search ?? strtolower(($option->name_ar ?? '').' '.($option->name_en ?? '').' '.$optionLabel);
                @endphp
                <button type="button"
                        role="option"
                        data-searchable-select-option
                        data-value="{{ $optionId }}"
                        data-label="{{ $optionLabel }}"
                        data-search="{{ $optionSearch }}"
                        @class([
                            'block w-full px-3 py-2 text-left text-sm hover:bg-slate-50',
                            'bg-slate-100 font-medium text-slate-900' => $optionId === $selectedValue,
                        ])>
                    <span dir="auto">{{ $optionLabel }}</span>
                </button>
            @endforeach
        </div>
        <p class="hidden px-3 py-2 text-sm text-slate-500" data-searchable-select-empty>No matches found.</p>
    </div>
</div>
