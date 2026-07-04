@php
    use App\Enums\Procurement\Flow\FlowStageState;

    $flow = $flow ?? null;

    $nodeClasses = [
        FlowStageState::Completed->value => 'border-emerald-300 bg-emerald-50 text-emerald-900',
        FlowStageState::Active->value => 'border-amber-200 bg-amber-50 text-amber-800',
        FlowStageState::Pending->value => 'border-dashed border-slate-300 bg-slate-50 text-slate-400',
        FlowStageState::Cancelled->value => 'border-red-300 bg-red-50 text-red-800',
    ];

    $dividerClasses = [
        FlowStageState::Completed->value => 'border-emerald-200',
        FlowStageState::Active->value => 'border-amber-100',
        FlowStageState::Pending->value => 'border-slate-200',
        FlowStageState::Cancelled->value => 'border-red-200',
    ];

    $metaClasses = [
        FlowStageState::Completed->value => 'text-emerald-700',
        FlowStageState::Active->value => 'text-amber-700',
        FlowStageState::Pending->value => 'text-slate-400',
        FlowStageState::Cancelled->value => 'text-red-600',
    ];

    $connectorClasses = [
        FlowStageState::Completed->value => 'text-slate-700',
        FlowStageState::Pending->value => 'text-slate-300',
    ];
@endphp

@if ($flow)
    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
        <div class="overflow-x-auto">
            <div class="flex min-w-[40rem] flex-row items-stretch justify-between gap-0 md:min-w-0">
                @foreach ($flow->stages as $index => $stage)
                    @php
                        $connectorState = $stage->state === FlowStageState::Completed
                            ? FlowStageState::Completed
                            : FlowStageState::Pending;

                        if ($stage->badge !== null && $stage->badgeLabel) {
                            $caption = $stage->badge.' '.$stage->badgeLabel;
                        } elseif ($stage->detail) {
                            $caption = $stage->detail;
                        } elseif ($stage->badge !== null) {
                            $caption = (string) $stage->badge;
                        } else {
                            $caption = $stage->state === FlowStageState::Pending ? '—' : '';
                        }
                    @endphp

                    <div class="flex w-[5.25rem] shrink-0 flex-col rounded-lg border px-2 py-2 sm:w-[6rem] {{ $nodeClasses[$stage->state->value] }}">
                        <div class="flex min-h-[2rem] flex-col items-center justify-center gap-1">
                            <div class="flex items-center gap-1">
                                @if ($stage->state === FlowStageState::Completed)
                                    <svg class="h-3.5 w-3.5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                                <span class="text-[10px] font-bold uppercase leading-tight tracking-wide sm:text-[11px]">{{ $stage->label }}</span>
                            </div>
                        </div>

                        <div class="my-1.5 border-t {{ $dividerClasses[$stage->state->value] }}"></div>

                        <p class="flex min-h-[2rem] items-center justify-center text-center text-[10px] leading-snug {{ $metaClasses[$stage->state->value] }}">
                            @if ($caption !== '')
                                <span class="line-clamp-2">{{ $caption }}</span>
                            @else
                                <span class="opacity-40">—</span>
                            @endif
                        </p>
                    </div>

                    @if ($index < count($flow->stages) - 1)
                        <div class="flex w-5 shrink-0 items-center justify-center sm:w-6 {{ $connectorClasses[$connectorState->value] }}">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif
