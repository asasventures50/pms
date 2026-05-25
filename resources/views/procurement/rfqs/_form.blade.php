@php
    $rfq = $rfq ?? null;
    $lineItems = old('items', $defaultItems ?? []);
    $rfqNumber = old('rfq_number', $rfq?->rfq_number ?? ($nextCode ?? ''));
@endphp

<article class="rfq-document mx-auto max-w-4xl border-2 border-slate-900 bg-white p-6 text-slate-900 shadow-sm sm:p-8">
    @include('procurement.rfqs._document-header')

    <div class="mt-6 space-y-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:gap-3">
            <span class="shrink-0 text-sm font-medium">RFQ No:</span>
            <span id="rfq_number" class="min-w-0 flex-1 border-b border-slate-900 pb-1 font-mono text-sm">{{ $rfqNumber }}</span>
            <input type="hidden" name="rfq_number" value="{{ $rfqNumber }}">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:gap-3">
                <label for="submission_deadline" class="shrink-0 text-sm font-medium">Submission Deadline:</label>
                <input type="date" name="submission_deadline" id="submission_deadline"
                       value="{{ old('submission_deadline', $rfq?->submission_deadline?->format('Y-m-d') ?? '') }}"
                       class="rfq-doc-field min-w-0 flex-1">
            </div>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:gap-3">
                <label for="issue_date" class="shrink-0 text-sm font-medium">Issue Date:</label>
                <input type="date" name="issue_date" id="issue_date"
                       value="{{ old('issue_date', $rfq?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="rfq-doc-field min-w-0 flex-1">
            </div>
        </div>
    </div>

    @include('procurement.rfqs._line-items', [
        'lineItems' => $lineItems,
        'prItemOptions' => $prItemOptions ?? [],
    ])

    @include('procurement.rfqs._terms', [
        'rfq' => $rfq,
        'rfqTerms' => $rfqTerms ?? ['general' => [], 'custom' => []],
        'scopeTermsMap' => $scopeTermsMap ?? [],
        'editable' => true,
    ])

    @include('procurement.rfqs._payment-terms', [
        'rfq' => $rfq,
        'paymentTerms' => $rfqPaymentTerms ?? [],
        'editable' => true,
    ])

    <script type="application/json" id="rfq-scope-terms-map">@json($scopeTermsMap ?? [])</script>

    @if (config('procurement.rfq.show_extended_form_fields'))
        @include('procurement.rfqs._form-extended', [
            'rfq' => $rfq,
            'vendors' => $vendors,
            'lineItems' => $lineItems,
        ])
    @endif

    <style>
        .rfq-doc-field {
            border: 0;
            border-bottom: 1px solid #0f172a;
            background: transparent;
            padding: 0.25rem 0;
            font-size: 0.875rem;
            line-height: 1.25rem;
            width: 100%;
        }
        .rfq-doc-field:focus {
            outline: none;
            box-shadow: none;
        }
        .rfq-doc-input {
            border: 0;
            background: transparent;
            border-radius: 0;
        }
        .rfq-doc-input:focus {
            outline: none;
            box-shadow: none;
        }
        @media print {
            .rfq-document {
                border-width: 1px;
                box-shadow: none;
                max-width: none;
            }
        }
    </style>
</article>

@push('scripts')
    @include('procurement.rfqs._form-scripts')
@endpush
