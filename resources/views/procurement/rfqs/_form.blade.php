@php
    use App\Support\Procurement\VendorQuotations\VendorQuotationValidityOptions;

    $rfq = $rfq ?? null;
    $lineItems = old('items', $defaultItems ?? []);
    $rfqNumber = old('rfq_number', $rfq?->rfq_number ?? ($nextCode ?? ''));
    $validityPreset = old('quotation_validity_preset', VendorQuotationValidityOptions::selectedDays($rfq?->quotation_validity));
    $deadlineTime = old('submission_deadline_time', $rfq?->submission_deadline_at?->format('H:i') ?? '17:00');
    $selectedTimezone = old('submission_timezone', $rfq?->submission_timezone ?? config('app.timezone'));
    $timezoneOptions = array_values(array_unique(array_filter([
        config('app.timezone'),
        'Asia/Amman',
        'Asia/Dubai',
        'Asia/Riyadh',
        'Europe/London',
        'UTC',
    ])));
@endphp

<div class="mx-auto max-w-5xl space-y-6">
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Request for Quotation</h2>
            <p class="text-sm text-slate-500">Procurement Department</p>
        </div>

        <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide text-slate-700">RFQ information</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Prepared by</label>
                <p class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                    {{ $rfq?->creator?->name ?? auth()->user()->name }}
                </p>
            </div>
            <div>
                <label for="rfq_number_display" class="block text-xs font-medium uppercase tracking-wide text-slate-500">RFQ number</label>
                <input type="text" id="rfq_number_display" readonly
                       value="{{ $rfqNumber }}"
                       class="admin-filter-control mt-1 font-mono bg-slate-50">
                <input type="hidden" name="rfq_number" value="{{ $rfqNumber }}">
            </div>
            <div>
                <label for="revision_number" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Revision number</label>
                <input type="number" name="revision_number" id="revision_number" min="0" max="999"
                       value="{{ old('revision_number', $rfq?->revision_number ?? 0) }}"
                       class="admin-filter-control mt-1 @error('revision_number') border-red-500 @enderror">
                @error('revision_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="issue_date" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Issue date</label>
                <input type="date" name="issue_date" id="issue_date"
                       value="{{ old('issue_date', $rfq?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="admin-filter-control mt-1 @error('issue_date') border-red-500 @enderror">
                @error('issue_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="submission_deadline" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Submission deadline (date)</label>
                <input type="date" name="submission_deadline" id="submission_deadline"
                       value="{{ old('submission_deadline', $rfq?->submission_deadline?->format('Y-m-d') ?? '') }}"
                       class="admin-filter-control mt-1 @error('submission_deadline') border-red-500 @enderror">
                @error('submission_deadline')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="submission_deadline_time" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Deadline time</label>
                <input type="time" name="submission_deadline_time" id="submission_deadline_time"
                       value="{{ $deadlineTime }}"
                       class="admin-filter-control mt-1 @error('submission_deadline_time') border-red-500 @enderror">
                @error('submission_deadline_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="submission_timezone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Timezone</label>
                <select name="submission_timezone" id="submission_timezone"
                        class="admin-filter-control mt-1 @error('submission_timezone') border-red-500 @enderror">
                    @foreach ($timezoneOptions as $tz)
                        <option value="{{ $tz }}" @selected($selectedTimezone === $tz)>{{ $tz }}</option>
                    @endforeach
                </select>
                @error('submission_timezone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="quotation_validity_preset" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Quotation validity</label>
                <select name="quotation_validity_preset" id="quotation_validity_preset"
                        class="admin-filter-control mt-1 @error('quotation_validity_preset') border-red-500 @enderror">
                    @foreach (VendorQuotationValidityOptions::dayOptions() as $days => $label)
                        <option value="{{ $days }}" @selected($validityPreset === (string) $days)>{{ $label }}</option>
                    @endforeach
                    <option value="custom" @selected($validityPreset === 'custom')>Custom</option>
                </select>
                @error('quotation_validity_preset')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="quotation_validity_custom_wrap" @class(['md:col-span-2', 'hidden' => $validityPreset !== 'custom'])>
                <label for="quotation_validity" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Custom validity text</label>
                <input type="text" name="quotation_validity" id="quotation_validity"
                       value="{{ old('quotation_validity', $rfq?->quotation_validity ?? '') }}"
                       placeholder="e.g. 45 days from submission"
                       class="admin-filter-control mt-1 @error('quotation_validity') border-red-500 @enderror">
                @error('quotation_validity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    @include('procurement._our-company', [
        'document' => $rfq,
        'variant' => 'admin-form',
    ])

    @include('procurement.rfqs._line-items', [
        'lineItems' => $lineItems,
        'prItemOptions' => $prItemOptions ?? [],
    ])

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('procurement.rfqs._terms', [
            'rfq' => $rfq,
            'rfqTerms' => $rfqTerms ?? ['general' => [], 'custom_rows' => []],
            'scopeTermsMap' => $scopeTermsMap ?? [],
            'editable' => true,
        ])
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('procurement.rfqs._payment-terms', [
            'rfq' => $rfq,
            'paymentTerms' => $rfqPaymentTerms ?? [],
            'editable' => true,
        ])
    </section>

    <script type="application/json" id="rfq-scope-terms-map">@json($scopeTermsMap ?? [])</script>

    @if (config('procurement.rfq.show_extended_form_fields'))
        @include('procurement.rfqs._form-extended', [
            'rfq' => $rfq,
            'vendors' => $vendors,
            'lineItems' => $lineItems,
        ])
    @endif
</div>

@push('scripts')
    @include('procurement.rfqs._form-scripts')
@endpush
