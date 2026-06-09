@php
    $name = $name ?? '';
    $label = $label ?? '';
    $value = $value ?? null;
@endphp

<div>
    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
    <div class="mt-2 flex gap-4">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="radio" name="{{ $name }}" value="1" @checked($value === true || $value === '1')> Yes
        </label>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="radio" name="{{ $name }}" value="0" @checked($value === false || $value === '0')> No
        </label>
    </div>
</div>
