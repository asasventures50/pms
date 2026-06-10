@props([
    'options' => [15, 30, 50, 75, 100],
    'param' => 'per_page',
    'label' => 'Per page',
    'id' => 'per_page_select',
])

@php
    $current = (int) request($param, $options[0]);
    if (! in_array($current, $options, true)) {
        $current = $options[0];
    }
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <label for="{{ $id }}" class="whitespace-nowrap text-sm text-gray-700 dark:text-gray-600">{{ $label }}</label>
    <select id="{{ $id }}"
            name="{{ $param }}"
            data-per-page-auto
            class="admin-filter-control !mt-0 h-9 min-w-[4.5rem] cursor-pointer py-1 text-sm">
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected($current === $option)>{{ $option }}</option>
        @endforeach
    </select>
</div>
