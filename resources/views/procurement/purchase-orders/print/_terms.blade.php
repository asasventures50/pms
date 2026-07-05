@php
    use App\Support\TextDirection;

    $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);

    $termsLocale = $termsLocale ?? $printLabels->locale();
    $termsRtl = $termsLocale === 'ar';
    $paymentTermsText = trim((string) ($purchaseOrder->payment_terms ?? ''));
    $showPaymentTerms = $purchaseOrder->show_payment_terms && $paymentTermsText !== '';
    $paymentTermsRtl = $paymentTermsText !== '' && TextDirection::isRtl($paymentTermsText);
    $notesText = trim((string) ($purchaseOrder->notes ?? ''));
    $notesRtl = $notesText !== '' && TextDirection::isRtl($notesText);
    $withTerms = $withTerms ?? true;
@endphp

<div class="po-section-title">{{ $printLabels->t('order_terms') }}</div>
@if ($purchaseOrder->handover_at)
    <div class="po-form-group po-form-group--row">
        <span class="po-form-label po-form-label--wide">{{ $printLabels->t('handover_date') }}</span>
        <span class="po-form-line po-form-line--flex">{{ $purchaseOrder->handover_at->format('d-m-Y') }}</span>
    </div>
@endif
@if ($purchaseOrder->dismantling_at)
    <div class="po-form-group po-form-group--row">
        <span class="po-form-label po-form-label--wide">{{ $printLabels->t('dismantling_date') }}</span>
        <span class="po-form-line po-form-line--flex">{{ $purchaseOrder->dismantling_at->format('d-m-Y') }}</span>
    </div>
@endif

@if ($showPaymentTerms)
    <div class="po-field-block">
        <div class="po-field-label">{{ $printLabels->t('payment_terms') }}</div>
        <div class="po-field-value" @if ($paymentTermsRtl) dir="rtl" lang="ar" @endif>{{ $paymentTermsText }}</div>
    </div>
@endif

@include('procurement.purchase-orders.print._commercial-terms', ['purchaseOrder' => $purchaseOrder])

<div class="po-field-block">
    <div class="po-field-label">{{ $printLabels->t('notes') }}</div>
    <div class="po-field-value" @if ($notesRtl) dir="rtl" lang="ar" @endif>{{ $notesText }}</div>
</div>

@if ($withTerms)
<div @class(['po-terms-block', 'po-terms-block--rtl' => $termsRtl]) @if ($termsRtl) dir="rtl" lang="ar" @endif>
    <div class="po-field-label">{{ $printLabels->t('terms_and_conditions') }}</div>
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
@endif
