@php
    $paymentTerms = $paymentTerms ?? [];
    $editable = $editable ?? false;
    $termsLocale = old('terms_locale', $rfq?->terms_locale ?? 'en');
@endphp

<section class="mt-8" @if ($editable) id="rfq-payment-terms-section" @endif>
    <div>
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Payment terms</h3>
        @if ($editable)
            <p class="mt-1 text-xs text-slate-500">Add payment terms for this RFQ (e.g. advance, milestones, net days).</p>
        @endif
    </div>

    @if ($editable)
        @error('payment_terms')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

        <ul id="rfq-payment-terms-list" class="mt-3 list-none space-y-2">
            @foreach ($paymentTerms as $index => $term)
                <li class="rfq-payment-term-row flex gap-2">
                    <span class="shrink-0 pt-2 text-sm text-slate-800">-</span>
                    <input type="text" name="payment_terms[{{ $index }}]" value="{{ $term }}"
                           class="rfq-doc-field rfq-payment-term-input min-w-0 flex-1 text-sm @error('payment_terms.'.$index) border-red-500 @enderror"
                           @if($termsLocale === 'ar') dir="rtl" @endif>
                    <button type="button" class="rfq-remove-payment-term shrink-0 rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50 print:hidden">Remove</button>
                </li>
            @endforeach
        </ul>
        <button type="button" id="rfq-add-payment-term"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
            Add payment term
        </button>
        <template id="rfq-payment-term-template">
            <li class="rfq-payment-term-row flex gap-2">
                <span class="shrink-0 pt-2 text-sm text-slate-800">-</span>
                <input type="text" data-name="payment_terms[]" value=""
                       class="rfq-doc-field rfq-payment-term-input min-w-0 flex-1 text-sm">
                <button type="button" class="rfq-remove-payment-term shrink-0 rounded-lg px-2 py-1 text-sm font-medium text-red-700 hover:bg-red-50">Remove</button>
            </li>
        </template>
    @else
        <ul class="mt-3 list-none space-y-1.5 text-sm text-slate-800">
            @forelse ($paymentTerms as $term)
                <li class="flex gap-2">
                    <span class="shrink-0">-</span>
                    <span @if(($rfq->terms_locale ?? 'en') === 'ar') dir="rtl" @endif>{{ $term }}</span>
                </li>
            @empty
                <li class="text-slate-500">No payment terms specified.</li>
            @endforelse
        </ul>
    @endif
</section>
