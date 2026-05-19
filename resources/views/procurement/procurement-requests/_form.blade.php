@php
    $procurementRequest = $procurementRequest ?? null;
    $lineItems = old('items', $defaultItems ?? []);
@endphp

<article class="pr-document mx-auto max-w-4xl space-y-6">
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @include('procurement.procurement-requests._document-header')
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Requestor information</h3>
        <input type="hidden" name="request_number" value="{{ old('request_number', $procurementRequest?->request_number ?? ($nextCode ?? '')) }}">
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="requestor_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Name</label>
                <input type="text" name="requestor_name" id="requestor_name"
                       value="{{ old('requestor_name', $procurementRequest?->requestor_name ?? auth()->user()->name) }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="requested_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Date</label>
                <input type="date" name="requested_at" id="requested_at"
                       value="{{ old('requested_at', $procurementRequest?->requested_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
        </div>
        <div class="mt-4">
            <label for="requestor_department" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Department</label>
            <input type="text" name="requestor_department" id="requestor_department"
                   value="{{ old('requestor_department', $procurementRequest?->requestor_department ?? '') }}"
                   class="admin-filter-control mt-1 w-full">
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('procurement.procurement-requests._line-items', ['lineItems' => $lineItems])
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Delivery requirements</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="required_delivery_date" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Required delivery date</label>
                <input type="date" name="required_delivery_date" id="required_delivery_date"
                       value="{{ old('required_delivery_date', $procurementRequest?->required_delivery_date?->format('Y-m-d') ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="delivery_location" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery location</label>
                <input type="text" name="delivery_location" id="delivery_location"
                       value="{{ old('delivery_location', $procurementRequest?->delivery_location ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Classification</h3>
        <textarea name="classification" id="classification" rows="3"
                  class="admin-filter-control mt-4 w-full resize-y">{{ old('classification', $procurementRequest?->classification ?? '') }}</textarea>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Supporting documents</h3>
        <textarea name="supporting_documents" id="supporting_documents" rows="3"
                  class="admin-filter-control mt-4 w-full resize-y"
                  placeholder="List attachments or notes about documents">{{ old('supporting_documents', $procurementRequest?->supporting_documents ?? '') }}</textarea>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Procurement department</h3>
        <div class="mt-4 space-y-4">
            <div>
                <label for="received_by" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Received by</label>
                <input type="text" name="received_by" id="received_by"
                       value="{{ old('received_by', $procurementRequest?->received_by ?? '') }}"
                       class="admin-filter-control mt-1 w-full">
            </div>
            <div>
                <label for="procurement_note" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Note</label>
                <textarea name="procurement_note" id="procurement_note" rows="2"
                          class="admin-filter-control mt-1 w-full resize-y">{{ old('procurement_note', $procurementRequest?->procurement_note ?? '') }}</textarea>
            </div>
        </div>
    </section>

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
    @include('procurement.procurement-requests._form-scripts')
@endpush
