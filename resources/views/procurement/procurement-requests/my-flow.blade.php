@extends('layouts.admin')

@section('title', ($viewAll ?? false) ? 'Request Tracking' : 'My Request Tracking')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                {{ ($viewAll ?? false) ? 'Request Tracking' : 'My Request Tracking' }}
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                @if ($viewAll ?? false)
                    Track where every procurement request stands in the pipeline.
                @else
                    Track where each of your procurement requests stands in the pipeline.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Dashboard</a>
            @if (auth()->user()->hasPermission('procurement-requests.create'))
                <a href="{{ route('procurement-requests.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                    New request
                </a>
            @endif
        </div>
    </div>

    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 text-sm shadow-sm">
        <p class="font-medium text-slate-900">Flowchart legend</p>
        <p class="mt-1 text-slate-600">Each request shows a flowchart: PR → RFQ → Quotations → Selection → PO → Invoice</p>
        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-600">
            <span class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 font-medium text-emerald-800">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Done
            </span>
            <span class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1.5 font-medium text-amber-800">
                Current
            </span>
            <span class="inline-flex items-center gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-2.5 py-1.5 font-medium text-slate-500">
                Pending
            </span>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($procurementRequests as $procurementRequest)
            @php
                $flow = $flows[$procurementRequest->id] ?? null;
            @endphp
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <a href="{{ route('procurement-requests.show', $procurementRequest) }}"
                           class="font-mono text-base font-semibold text-slate-900 hover:text-indigo-700">
                            {{ $procurementRequest->request_number }}
                        </a>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $procurementRequest->requested_at?->format('Y-m-d') ?? '—' }}
                            @if ($procurementRequest->project)
                                · {{ $procurementRequest->project->code }}
                            @endif
                            @if ($viewAll ?? false)
                                · {{ $procurementRequest->creator?->name ?? '—' }}
                            @endif
                            · <span class="capitalize">{{ $procurementRequest->status->value }}</span>
                        </p>
                        @if ($flow)
                            <p class="mt-2 text-sm font-medium text-amber-700">{{ $flow->statusSummary() }}</p>
                        @endif
                    </div>
                    <a href="{{ route('procurement-requests.show', $procurementRequest) }}"
                       class="inline-flex shrink-0 items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-800 hover:bg-slate-50">
                        View request
                    </a>
                </div>

                @if ($flow)
                    @include('procurement.procurement-requests._flow-pipeline', [
                        'flow' => $flow,
                        'compact' => false,
                    ])
                @endif
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                <p class="text-sm font-medium text-slate-900">
                    {{ ($viewAll ?? false) ? 'No procurement requests found' : 'No procurement requests yet' }}
                </p>
                <p class="mt-1 text-sm text-slate-600">
                    @if ($viewAll ?? false)
                        Requests will appear here once they are created in the system.
                    @else
                        Create a request to start tracking its progress here.
                    @endif
                </p>
                @if (auth()->user()->hasPermission('procurement-requests.create'))
                    <a href="{{ route('procurement-requests.create') }}"
                       class="mt-4 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Create procurement request
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    @if ($procurementRequests->hasPages())
        <div class="mt-6">
            {{ $procurementRequests->links() }}
        </div>
    @endif
@endsection
