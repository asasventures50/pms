@php
    use App\Support\Access\UserDepartment;

    $procurementRequest = $procurementRequest ?? null;
    $formDefaults = $formDefaults ?? [];
    $authUser = auth()->user();
    $requestorName = $procurementRequest?->requestor_name ?? $authUser->name;
    $requestedAt = $procurementRequest?->requested_at?->format('Y-m-d') ?? now()->format('Y-m-d');
    $requestorDepartment = $procurementRequest?->requestor_department
        ?? UserDepartment::label($authUser->department ?? UserDepartment::DEFAULT);
@endphp

<article class="pr-document mx-auto max-w-5xl space-y-6">
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @include('procurement.procurement-requests._document-header', [
            'formDefaults' => $formDefaults,
            'nextCode' => $nextCode ?? null,
        ])
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Requester information</h3>
        <input type="hidden" name="request_number" id="request_number"
               value="{{ old('request_number', $procurementRequest?->request_number ?? '') }}">
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Name</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $requestorName }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Date</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $requestedAt }}</dd>
            </div>
        </dl>
        <div class="mt-4">
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Department</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ $requestorDepartment }}</dd>
        </div>
    </section>

    @include('procurement.procurement-requests._pr-information', [
        'formDefaults' => $formDefaults,
        'projects' => $projects ?? collect(),
        'categories' => $categories ?? collect(),
    ])

    @include('procurement.procurement-requests._boq', [
        'formDefaults' => $formDefaults,
        'projects' => $projects ?? collect(),
    ])

    @include('procurement.procurement-requests._justification-delivery', [
        'formDefaults' => $formDefaults,
    ])

    @include('procurement.procurement-requests._supporting-documents', [
        'formDefaults' => $formDefaults,
    ])

    @include('procurement.procurement-requests._payment-terms', [
        'formDefaults' => $formDefaults,
    ])

    @include('procurement.procurement-requests._retentions', [
        'formDefaults' => $formDefaults,
    ])

    @include('procurement.procurement-requests._maintenance', [
        'formDefaults' => $formDefaults,
    ])

    @include('procurement.procurement-requests._internal-sections', [
        'formDefaults' => $formDefaults,
    ])

    @if ($procurementRequest?->exists)
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm print:hidden">
            <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
            <select name="status" id="status" class="admin-filter-control mt-1 max-w-xs">
                @foreach (\App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $procurementRequest->status->value) === $status->value)>
                        {{ ucfirst($status->value) }}
                    </option>
                @endforeach
            </select>
        </section>
    @endif
</article>

@push('scripts')
    @include('procurement.procurement-requests._form-scripts', [
        'categories' => $categories ?? collect(),
    ])
@endpush
