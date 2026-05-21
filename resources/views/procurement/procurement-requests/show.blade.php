@extends('layouts.admin')

@section('title', 'Procurement Request '.$procurementRequest->request_number)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $procurementRequest->request_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->hasPermission('procurement-requests.update'))
                <a href="{{ route('procurement-requests.edit', $procurementRequest) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            @endif
            <a href="{{ route('procurement-requests.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back</a>
            <button type="button" onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Print</button>
        </div>
    </div>

    <article class="mx-auto max-w-4xl space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 print:border print:shadow-none">
            @include('procurement.procurement-requests._document-header')
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Requestor information</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Name</dt>
                    <dd class="mt-0.5">{{ $procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Date</dt>
                    <dd class="mt-0.5">{{ $procurementRequest->requested_at?->format('Y-m-d') ?? '—' }}</dd>
                </div>
            </dl>
            <div class="mt-4">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Department</dt>
                <dd class="mt-0.5">{{ $procurementRequest->requestor_department ?: '—' }}</dd>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Procurement details</h3>
            @if ($procurementRequest->items->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No line items.</p>
            @else
                <div class="mt-4 space-y-4">
                    @foreach ($procurementRequest->items as $index => $line)
                        @include('procurement.procurement-requests._line-item-show', [
                            'index' => $index,
                            'line' => $line,
                            'procurementRequest' => $procurementRequest,
                        ])
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Delivery requirements</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Required delivery date</dt>
                    <dd class="mt-0.5">{{ $procurementRequest->required_delivery_date?->format('m/d/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Delivery location</dt>
                    <dd class="mt-0.5">{{ $procurementRequest->delivery_location ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Is Delivered</dt>
                    <dd class="mt-0.5">{{ $procurementRequest->delivery_completed ? 'Yes' : 'No' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Classification</h3>
            <p class="mt-4 text-slate-900">{{ $procurementRequest->classification ?: '—' }}</p>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="text-sm font-semibold text-slate-900">Supporting documents</h3>
            @if ($procurementRequest->supporting_document_path)
                <p class="mt-4">
                    <a href="{{ $procurementRequest->supportingDocumentUrl() }}" target="_blank" rel="noopener"
                       class="font-medium text-slate-900 underline hover:text-slate-700">
                        {{ $procurementRequest->supporting_document_name ?: 'Download file' }}
                    </a>
                </p>
            @else
                <p class="mt-4 text-slate-900">—</p>
            @endif
        </section>

        <p class="text-xs text-slate-500 print:hidden">Status: {{ ucfirst($procurementRequest->status->value) }}</p>
    </article>
@endsection
