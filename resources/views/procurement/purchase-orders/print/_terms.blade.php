@php
    use App\Support\TextDirection;

    $termsLocale = $termsLocale ?? ($purchaseOrder->terms_locale ?? 'en');
    $termsRtl = $termsLocale === 'ar';
    $paymentTermsText = trim((string) ($purchaseOrder->payment_terms ?? ''));
    $paymentTermsRtl = $paymentTermsText !== '' && TextDirection::isRtl($paymentTermsText);
    $notesText = trim((string) ($purchaseOrder->notes ?? ''));
    $notesRtl = $notesText !== '' && TextDirection::isRtl($notesText);
@endphp

<div class="po-section-title">Order terms</div>
@if ($purchaseOrder->handover_at)
    <div class="po-form-group po-form-group--row">
        <span class="po-form-label po-form-label--wide">Handover date (maintenance from):</span>
        <span class="po-form-line po-form-line--flex">{{ $purchaseOrder->handover_at->format('d-m-Y') }}</span>
    </div>
@endif
@if ($purchaseOrder->dismantling_at)
    <div class="po-form-group po-form-group--row">
        <span class="po-form-label po-form-label--wide">Dismantling date (if any):</span>
        <span class="po-form-line po-form-line--flex">{{ $purchaseOrder->dismantling_at->format('d-m-Y') }}</span>
    </div>
@endif

<div class="po-field-block">
    <div class="po-field-label">Payment terms:</div>
    <div class="po-field-value" @if ($paymentTermsRtl) dir="rtl" lang="ar" @endif>{{ $paymentTermsText }}</div>
</div>

@include('procurement.purchase-orders.print._commercial-terms', ['purchaseOrder' => $purchaseOrder])

<div class="po-field-block">
    <div class="po-field-label">Notes:</div>
    <div class="po-field-value" @if ($notesRtl) dir="rtl" lang="ar" @endif>{{ $notesText }}</div>
</div>

<div @class(['po-terms-block', 'po-terms-block--rtl' => $termsRtl]) @if ($termsRtl) dir="rtl" lang="ar" @endif>
    <div class="po-field-label">Terms and conditions :</div>
    @if (count($terms) > 0)
        <ul class="po-terms-list">
            @foreach ($terms as $term)
                @php
                    $termText = trim((string) $term);
                    $parts = explode(':', $termText, 2);
                    $hasKeyValue = count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '';
                    $termKey = $hasKeyValue ? trim($parts[0]) : '';
                    $termValue = $hasKeyValue ? trim($parts[1]) : $termText;
                @endphp
                <li @if ($termsRtl) lang="ar" @endif>
                    @if ($hasKeyValue)
                        <strong class="po-term-key">{{ $termKey }}:</strong> {{ $termValue }}
                    @else
                        {{ $termText }}
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <div class="po-field-value po-field-value--empty"></div>
        <div class="po-field-value po-field-value--empty"></div>
    @endif
</div>
