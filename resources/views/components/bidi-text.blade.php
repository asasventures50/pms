@props([
    'tag' => 'span',
    'preWrap' => false,
])

@php
    $classes = trim(implode(' ', array_filter([
        $attributes->get('class'),
        'text-start',
        $preWrap ? 'whitespace-pre-wrap' : null,
    ])));
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => $classes, 'dir' => 'auto']) }}>{{ $slot }}</{{ $tag }}>
