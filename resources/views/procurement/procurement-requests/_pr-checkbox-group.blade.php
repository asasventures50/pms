@php
    $name = $name ?? '';
    $label = $label ?? '';
    $options = $options ?? [];
    $selected = $selected ?? [];
    $required = $required ?? false;
    $hint = $hint ?? null;
@endphp

<div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
        {{ $label }}
        @if ($required)<span class="text-red-600">*</span>@endif
    </p>
    @if ($hint)<p class="mt-1 text-xs normal-case text-slate-500">{{ $hint }}</p>@endif
    <div @class([
        'mt-3 flex flex-wrap gap-2',
        'rounded-md ring-1 ring-red-500 ring-offset-2' => $errors->has($name) || $errors->has($name.'.*'),
    ]) role="group" aria-label="{{ $label }}">
        @foreach ($options as $option)
            @php
                $value = is_object($option) ? $option->value : $option['value'];
                $optionLabel = is_object($option) ? $option->label() : $option['label'];
            @endphp
            <label class="inline-flex cursor-pointer select-none items-center rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-900 has-[:checked]:text-white has-[:checked]:shadow-md">
                <input type="checkbox"
                       name="{{ $name }}[]"
                       value="{{ $value }}"
                       class="sr-only"
                       @checked(in_array($value, $selected, true))>
                <span>{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
    @error($name)<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    @error($name.'.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
