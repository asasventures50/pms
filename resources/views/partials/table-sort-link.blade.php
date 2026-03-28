{{--
    Sortable table header link. Expects: $route (name), $column, $label, $sortColumn, $sortDirection
--}}
@php
    $href = route($route, \App\Support\TableSort::queryForColumn(
        request()->query(),
        $column,
        $sortColumn,
        $sortDirection
    ));
    $active = $sortColumn === $column;
@endphp
<a href="{{ $href }}"
   class="group inline-flex cursor-pointer items-center gap-1 rounded px-1 py-0.5 -mx-1 -my-0.5 transition-colors hover:bg-slate-100 {{ $active ? 'font-semibold text-slate-900' : 'text-slate-500 hover:text-slate-800' }}"
   title="Sort by {{ $label }}">
    <span>{{ $label }}</span>
    @if ($active)
        <span class="text-slate-700" aria-hidden="true">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
    @else
        <span class="text-slate-300 opacity-0 transition-opacity group-hover:opacity-100" aria-hidden="true">↕</span>
    @endif
</a>
