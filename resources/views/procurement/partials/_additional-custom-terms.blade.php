@php
    $customRows = $customRows ?? [];
    $termsLocale = $termsLocale ?? 'en';
    $listId = $listId ?? 'custom-terms-list';
    $templateId = $templateId ?? 'custom-term-template';
    $addButtonId = $addButtonId ?? 'add-custom-term';
    $rowClass = $rowClass ?? 'custom-term-row';
    $removeClass = $removeClass ?? 'remove-custom-term';
    $inputClass = $inputClass ?? 'admin-filter-control';
    $scopeLabel = $scopeLabel ?? 'this document only';
    $headingTag = $headingTag ?? 'h3';
@endphp

<div class="mt-6">
    <{{ $headingTag }} class="text-xs font-semibold uppercase tracking-wide text-slate-600">
        Additional terms <span class="font-normal normal-case text-slate-500">({{ $scopeLabel }})</span>
    </{{ $headingTag }}>
    <p class="mt-1 text-xs text-slate-500 print:hidden">Optional key and value. The key prints in bold. Leave key empty for free text only.</p>
    <ul id="{{ $listId }}" class="mt-2 list-none space-y-2">
        @foreach ($customRows as $index => $row)
            <li class="{{ $rowClass }} rounded-lg border border-slate-200 bg-slate-50 p-3 print:border-0 print:bg-transparent print:p-0">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start print:hidden">
                    <div class="min-w-0 flex-1 space-y-2">
                        <input type="text" name="terms_custom[{{ $index }}][key]" value="{{ $row['key'] ?? '' }}"
                               placeholder="Key (optional)"
                               class="custom-term-key {{ $inputClass }} w-full text-sm @error('terms_custom.'.$index.'.key') border-red-500 @enderror"
                               @if($termsLocale === 'ar') dir="rtl" @endif>
                        @error('terms_custom.'.$index.'.key')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        <textarea name="terms_custom[{{ $index }}][value]" rows="2"
                                  placeholder="Value / text"
                                  class="custom-term-value {{ $inputClass }} w-full text-sm @error('terms_custom.'.$index.'.value') border-red-500 @enderror"
                                  @if($termsLocale === 'ar') dir="rtl" @endif>{{ $row['value'] ?? '' }}</textarea>
                        @error('terms_custom.'.$index.'.value')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="button" class="{{ $removeClass }} shrink-0 rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50">Remove</button>
                </div>
            </li>
        @endforeach
    </ul>
    <button type="button" id="{{ $addButtonId }}"
            class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
        Add term
    </button>
    <template id="{{ $templateId }}">
        <li class="{{ $rowClass }} rounded-lg border border-slate-200 bg-slate-50 p-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                <div class="min-w-0 flex-1 space-y-2">
                    <input type="text" data-name="key" value="" placeholder="Key (optional)"
                           class="custom-term-key {{ $inputClass }} w-full text-sm"
                           @if($termsLocale === 'ar') dir="rtl" @endif>
                    <textarea data-name="value" rows="2" placeholder="Value / text"
                              class="custom-term-value {{ $inputClass }} w-full text-sm"
                              @if($termsLocale === 'ar') dir="rtl" @endif></textarea>
                </div>
                <button type="button" class="{{ $removeClass }} shrink-0 rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50">Remove</button>
            </div>
        </li>
    </template>
</div>
