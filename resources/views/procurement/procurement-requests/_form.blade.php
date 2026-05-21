@php
    use App\Support\Access\UserDepartment;

    $procurementRequest = $procurementRequest ?? null;
    $lineItems = old('items', $defaultItems ?? []);
    $authUser = auth()->user();
    $requestorName = $procurementRequest?->requestor_name ?? $authUser->name;
    $requestedAt = $procurementRequest?->requested_at?->format('Y-m-d') ?? now()->format('Y-m-d');
    $requestorDepartment = $procurementRequest?->requestor_department
        ?? UserDepartment::label($authUser->department ?? UserDepartment::DEFAULT);
    $flexibleDeliveryDate = (bool) old(
        'flexible_delivery_date',
        $procurementRequest?->flexible_delivery_date ?? true
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
        @include('procurement.procurement-requests._line-items', [
            'lineItems' => $lineItems,
            'projects' => $projects ?? collect(),
        ])
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
                    <input type="checkbox" name="flexible_delivery_date" id="flexible_delivery_date" value="1"
                           @checked($flexibleDeliveryDate)
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    <span>Flexible delivery date</span>
                </label>
                <p class="mt-1 text-xs text-slate-500">When enabled, a fixed delivery date is optional.</p>
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
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Supporting documents</h3>
                <p class="mt-1 text-xs text-slate-500">Add one file at a time · max 10 MB each</p>
            </div>
            <button type="button" id="pr-add-supporting-file"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
                <span class="text-base leading-none" aria-hidden="true">+</span>
                Add file
            </button>
        </div>

        @if ($procurementRequest?->documents?->isNotEmpty())
            <ul class="mt-4 space-y-2">
                @foreach ($procurementRequest->documents as $document)
                    <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm print:border">
                        <a href="{{ $document->url }}" target="_blank" rel="noopener"
                           class="min-w-0 truncate font-medium text-slate-900 hover:underline">
                            {{ $document->file_name }}
                        </a>
                        <label class="flex shrink-0 cursor-pointer items-center gap-2 text-xs text-slate-600 print:hidden">
                            <input type="checkbox" name="remove_supporting_document_ids[]" value="{{ $document->id }}"
                                   class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                            <span>Remove</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-4 space-y-2 print:hidden @error('supporting_documents') rounded-lg border border-red-300 bg-red-50/30 p-3 @enderror">
            <p class="text-xs text-slate-500">PDF · Word · Excel · JPG · PNG · WebP</p>
            <div id="pr-supporting-files-body" class="space-y-2"></div>
            <template id="pr-supporting-file-template">
                <div class="pr-supporting-file-row flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-slate-50/50 px-3 py-2">
                    <input type="file" name="supporting_documents[]"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp"
                           class="block max-w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800">
                    <span class="pr-supporting-file-name min-w-0 flex-1 truncate text-sm text-slate-600"></span>
                    <button type="button"
                            class="pr-remove-supporting-file shrink-0 text-sm font-medium text-red-700 hover:text-red-900">
                        Remove
                    </button>
                </div>
            </template>
            @error('supporting_documents')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            @error('supporting_documents.*')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
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
