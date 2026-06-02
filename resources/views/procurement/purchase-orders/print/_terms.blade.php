@php
    $termsLocale = $termsLocale ?? ($purchaseOrder->terms_locale ?? 'en');
    $termsRtl = $termsLocale === 'ar';
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
    <div class="po-field-value">{{ $purchaseOrder->payment_terms ?? '' }}</div>
</div>

<div class="po-field-block">
    <div class="po-field-label">Notes:</div>
    <div class="po-field-value">{{ $purchaseOrder->notes ?? '' }}</div>
</div>

<div @class(['po-terms-block', 'po-terms-block--rtl' => $termsRtl]) @if ($termsRtl) dir="rtl" lang="ar" @endif>
    <div class="po-field-label">Terms and conditions :</div>
    @if (count($terms) > 0)
        <ul class="po-terms-list">
            @foreach ($terms as $term)
                <li @if ($termsRtl) lang="ar" @endif>{{ $term }}</li>
            @endforeach
        </ul>
    @else
        <div class="po-field-value po-field-value--empty"></div>
        <div class="po-field-value po-field-value--empty"></div>
    @endif
</div>
