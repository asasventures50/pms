@php
    $index = $index ?? 0;
    $line = $line ?? null;
    $lineNo = $line?->line_number;
    if (($lineNo === null || $lineNo === '') && $line && $procurementRequest) {
        $lineNo = \App\Services\Procurement\ProcurementRequests\ProcurementRequestLineNumberFormatter::format(
            $procurementRequest->request_number,
            $index
        );
    }
@endphp

<article class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
    <p class="mb-3 text-sm font-semibold text-slate-900">
        Line <span class="font-mono text-xs">{{ $lineNo ?: '—' }}</span>
    </p>
    <dl class="grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Project</dt>
            <dd class="mt-0.5 text-slate-900">
                @if ($line->project)
                    {{ $line->project->code }} — {{ $line->project->name }}
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Zone</dt>
            <dd class="mt-0.5 text-slate-900">
                @if ($line->zone)
                    {{ $line->zone->code }} — {{ $line->zone->name }}
                @else
                    —
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</dt>
            <dd class="mt-0.5 text-slate-900">{{ $line->category ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Sub category</dt>
            <dd class="mt-0.5 text-slate-900">{{ $line->subcategory ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Scope type</dt>
            <dd class="mt-0.5 text-slate-900">{{ $line->scope_type ?: '—' }}</dd>
        </div>
    </dl>
    <div class="mt-3">
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Item description</dt>
        <dd class="mt-0.5 text-slate-900">{{ $line->description }}</dd>
    </div>
    <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Unit</dt>
            <dd class="mt-0.5 text-slate-900">{{ $line->unit ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Quantity</dt>
            <dd class="mt-0.5 text-slate-900">{{ number_format($line->quantity, 3) }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Justification</dt>
            <dd class="mt-0.5 text-slate-900">{{ $line->justification ?: '—' }}</dd>
        </div>
    </dl>
</article>
