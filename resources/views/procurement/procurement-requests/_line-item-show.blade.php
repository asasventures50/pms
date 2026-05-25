@php
    use App\Support\Procurement\ProcurementScopeType;

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
            <dd class="mt-0.5 text-slate-900">{{ ProcurementScopeType::display($line->scope_type) ?: '—' }}</dd>
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
    <div class="mt-3">
        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Scope of work</dt>
        <dd class="mt-0.5 whitespace-pre-wrap text-slate-900">{{ $line->scope_of_work ?: '—' }}</dd>
    </div>

    <div class="mt-4 border-t border-slate-200 pt-4">
        <h4 class="text-sm font-semibold text-slate-900">Delivery requirements</h4>
        <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Required delivery date</dt>
                <dd class="mt-0.5 text-slate-900">{{ $line->required_delivery_date?->format('m/d/Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Delivery location</dt>
                <dd class="mt-0.5 text-slate-900">{{ $line->delivery_location ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Flexible delivery date</dt>
                <dd class="mt-0.5 text-slate-900">{{ $line->flexible_delivery_date ? 'Yes' : 'No' }}</dd>
            </div>
        </dl>
    </div>

    <div class="mt-4 border-t border-slate-200 pt-4">
        <h4 class="text-sm font-semibold text-slate-900">Supporting documents</h4>
        @if ($line->documents->isEmpty())
            <p class="mt-2 text-sm text-slate-900">—</p>
        @else
            <ul class="mt-2 space-y-2">
                @foreach ($line->documents as $document)
                    <li>
                        <a href="{{ $document->url }}" target="_blank" rel="noopener"
                           class="text-sm font-medium text-slate-900 underline hover:text-slate-700">
                            {{ $document->file_name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</article>
