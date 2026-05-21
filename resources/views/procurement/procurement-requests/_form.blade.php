@php
    use App\Support\Access\UserDepartment;

    $procurementRequest = $procurementRequest ?? null;
    $lineItems = old('items', $defaultItems ?? []);
    $authUser = auth()->user();
    $requestorName = $procurementRequest?->requestor_name ?? $authUser->name;
    $requestedAt = $procurementRequest?->requested_at?->format('Y-m-d') ?? now()->format('Y-m-d');
    $requestorDepartment = $procurementRequest?->requestor_department
        ?? UserDepartment::label($authUser->department ?? UserDepartment::DEFAULT);
    $deliveryCompleted = (bool) old(
        'delivery_completed',
        $procurementRequest?->delivery_completed ?? false
    );
    $requiredDeliveryDateValue = old('required_delivery_date');
    if ($requiredDeliveryDateValue === null && $procurementRequest?->required_delivery_date) {
        $requiredDeliveryDateValue = $procurementRequest->required_delivery_date->format('Y-m-d');
    }
@endphp

<article class="pr-document mx-auto max-w-4xl space-y-6">
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @include('procurement.procurement-requests._document-header')
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Requestor information</h3>
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

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('procurement.procurement-requests._line-items', ['lineItems' => $lineItems])
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-slate-900">Delivery requirements</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="required_delivery_date" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Required delivery date</label>
                <input type="date" name="required_delivery_date" id="required_delivery_date"
                       value="{{ $requiredDeliveryDateValue ?? '' }}"
                       class="admin-filter-control mt-1 w-full max-w-xs">
                @error('required_delivery_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <label class="mt-3 flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="delivery_completed" id="delivery_completed" value="1"
                           @checked($deliveryCompleted)
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    <span>Is Delivered</span>
                </label>
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
        <h3 class="text-sm font-semibold text-slate-900">Note</h3>
        <input type="text" name="classification" id="classification"
               value="{{ old('classification', $procurementRequest?->classification ?? '') }}"
               class="admin-filter-control mt-4 w-full">
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Supporting documents</h3>
            <p class="text-xs text-slate-500">One file per request · max 10 MB</p>
        </div>

        @if ($procurementRequest?->supporting_document_path)
            <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50/80 p-4 print:border">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500"
                         aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Current file</p>
                        <a href="{{ $procurementRequest->supportingDocumentUrl() }}" target="_blank" rel="noopener"
                           class="mt-1 block truncate text-sm font-medium text-slate-900 hover:text-slate-700 hover:underline">
                            {{ $procurementRequest->supporting_document_name ?: 'Download' }}
                        </a>
                    </div>
                    <a href="{{ $procurementRequest->supportingDocumentUrl() }}" target="_blank" rel="noopener"
                       class="shrink-0 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100 print:hidden">
                        Open
                    </a>
                </div>
                <label class="mt-4 flex cursor-pointer items-center gap-2 border-t border-slate-200/80 pt-3 text-sm text-slate-600 print:hidden">
                    <input type="checkbox" name="remove_supporting_document" value="1"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    <span>Remove this file when saving</span>
                </label>
            </div>
        @endif

        <div class="mt-4 print:hidden">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                {{ $procurementRequest?->supporting_document_path ? 'Replace with a new file' : 'Upload file' }}
            </p>
            <label for="supporting_document"
                   class="group mt-2 flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/50 px-6 py-8 text-center transition-colors hover:border-slate-400 hover:bg-slate-50 focus-within:border-slate-500 focus-within:ring-2 focus-within:ring-slate-500/20 @error('supporting_document') border-red-400 bg-red-50/30 @enderror">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm ring-1 ring-slate-200 transition-colors group-hover:text-slate-600"
                      aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </span>
                <span class="mt-3 text-xs text-slate-500">or drag and drop here</span>
                <span class="mt-2 text-xs text-slate-400">PDF · Word · Excel · JPG · PNG · WebP</span>
                <input type="file" name="supporting_document" id="supporting_document"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp"
                       class="sr-only">
            </label>
            <p id="pr-supporting-document-name" class="mt-2 hidden text-sm text-slate-700">
                <span class="font-medium text-slate-500">Selected:</span>
                <span data-filename></span>
            </p>
            @error('supporting_document')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
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
