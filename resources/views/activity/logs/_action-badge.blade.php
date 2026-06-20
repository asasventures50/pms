@props(['action', 'tone' => 'default'])

@php
    $styles = match ($tone) {
        'create' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'update' => 'border-sky-200 bg-sky-50 text-sky-800',
        'delete' => 'border-rose-200 bg-rose-50 text-rose-800',
        'restore' => 'border-amber-200 bg-amber-50 text-amber-800',
        'login' => 'border-violet-200 bg-violet-50 text-violet-800',
        'logout' => 'border-slate-200 bg-slate-100 text-slate-700',
        default => 'border-slate-200 bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-0.5 font-mono text-[11px] font-semibold {$styles}"]) }}>
    {{ $action }}
</span>
