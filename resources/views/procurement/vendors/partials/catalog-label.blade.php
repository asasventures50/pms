@props(['model'])
@if ($model)
    <div class="text-slate-900">{{ $model->name_ar }}</div>
    @if (filled($model->name_en))
        <div class="text-xs text-slate-500">{{ $model->name_en }}</div>
    @endif
@else
    —
@endif
