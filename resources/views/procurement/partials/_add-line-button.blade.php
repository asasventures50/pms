@props([
    'id' => 'add-line',
    'label' => 'Add line',
])

<div {{ $attributes->merge(['class' => 'procurement-add-line-bar mt-6 border-t-2 border-slate-300 pt-5']) }}>
    <button type="button" id="{{ $id }}">
        <span class="text-lg font-bold leading-none" aria-hidden="true">+</span>
        <span>{{ $label }}</span>
    </button>
</div>
