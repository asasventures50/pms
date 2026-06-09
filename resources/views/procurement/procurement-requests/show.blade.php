@php
    use App\Enums\Procurement\PrCompany;
    use App\Enums\Procurement\ProcurementRequests\GeographicScope;
    use App\Enums\Procurement\ProcurementRequests\ProcurementType;
    use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
    use App\Support\Procurement\ProcurementCheckboxGroup;

    $formData = $formData ?? [];
@endphp

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
            <a href="{{ route('procurement-requests.print', ['procurement_request' => $procurementRequest, 'locale' => 'en']) }}" target="_blank" rel="noopener"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Print (EN)</a>
            <a href="{{ route('procurement-requests.print', ['procurement_request' => $procurementRequest, 'locale' => 'ar']) }}" target="_blank" rel="noopener"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Print (AR)</a>
        </div>
    </div>

    <article class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('procurement.procurement-requests._document-header')
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="font-semibold text-slate-900">Requester information</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs uppercase text-slate-500">Name</dt><dd class="mt-0.5">{{ $procurementRequest->requestor_name ?? $procurementRequest->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Date</dt><dd class="mt-0.5">{{ $procurementRequest->requested_at?->format('Y-m-d') ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">Department</dt><dd class="mt-0.5">{{ $procurementRequest->requestor_department ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="font-semibold text-slate-900">PR information</h3>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs uppercase text-slate-500">Company</dt><dd class="mt-0.5">{{ PrCompany::resolve($procurementRequest->company_key)->label() }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Project</dt><dd class="mt-0.5">@if ($procurementRequest->project){{ $procurementRequest->project->code }} — {{ $procurementRequest->project->name }}@else — @endif</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Zone</dt><dd class="mt-0.5">@if ($procurementRequest->zone){{ $procurementRequest->zone->code }} — {{ $procurementRequest->zone->name }}@else — @endif</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Category</dt><dd class="mt-0.5">{{ $procurementRequest->category?->name_en ?? $formData['legacy_category'] ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Subcategory</dt><dd class="mt-0.5">{{ $procurementRequest->subcategory?->name_en ?? $formData['legacy_subcategory'] ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Procurement type</dt><dd class="mt-0.5">{{ ProcurementCheckboxGroup::display($procurementRequest->procurement_types, ProcurementType::values(), fn ($v) => ProcurementType::from($v)->label()) ?: '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Scope</dt><dd class="mt-0.5">{{ GeographicScope::display($procurementRequest->geographic_scopes) ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs uppercase text-slate-500">Vendor type</dt><dd class="mt-0.5">{{ ProcurementCheckboxGroup::display($procurementRequest->vendor_types, ProcurementVendorType::values(), fn ($v) => ProcurementVendorType::from($v)->label()) ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="font-semibold text-slate-900">BOQ @if ($procurementRequest->currency_code)<span class="font-normal text-slate-500">· {{ $procurementRequest->currency_code }}</span>@endif</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-500"><tr><th class="py-2 pr-3">Item</th><th class="py-2 pr-3">Description</th><th class="py-2 pr-3">Qty</th><th class="py-2 pr-3">Unit</th><th class="py-2 pr-3">Unit price</th><th class="py-2">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse ($procurementRequest->items as $line)
                        <tr>
                            <td class="py-2 pr-3">{{ filled($line->item_name) ? $line->item_name : ($line->line_number ?: '—') }}</td>
                            <td class="py-2 pr-3">{{ $line->description }}</td>
                            <td class="py-2 pr-3">{{ number_format($line->quantity, 3) }}</td>
                            <td class="py-2 pr-3">{{ $line->unit ?: '—' }}</td>
                            <td class="py-2 pr-3">{{ number_format((float) $line->unit_price, 4) }}</td>
                            <td class="py-2">{{ number_format((float) ($line->total_price ?? 0), 4) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-4 text-slate-500">No BOQ lines.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-slate-500">Samples required: @if ($procurementRequest->samples_required === null)—@elseif ($procurementRequest->samples_required)Yes@else No @endif</p>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 text-sm shadow-sm">
            <h3 class="font-semibold text-slate-900">Justification & delivery</h3>
            <p class="mt-3 whitespace-pre-wrap">{{ $formData['justification'] ?: '—' }}</p>
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                <div><dt class="text-xs uppercase text-slate-500">Lead time (days)</dt><dd>{{ $procurementRequest->delivery_lead_time_days ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Delivery location</dt><dd>{{ $formData['delivery_location'] ?: '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-slate-500">Flexible delivery</dt><dd>{{ ($formData['flexible_delivery_date'] ?? true) ? 'Yes' : 'No' }}</dd></div>
            </dl>
            <div class="mt-4"><dt class="text-xs uppercase text-slate-500">Scope of work</dt><dd class="mt-1 whitespace-pre-wrap">{{ $formData['scope_of_work'] ?: '—' }}</dd></div>
        </section>

        @include('procurement.procurement-requests._show-sections', ['procurementRequest' => $procurementRequest, 'formData' => $formData])

        <p class="text-xs text-slate-500 print:hidden">Status: {{ ucfirst($procurementRequest->status->value) }}</p>
    </article>
@endsection
