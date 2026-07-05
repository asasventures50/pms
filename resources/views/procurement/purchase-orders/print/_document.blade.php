@php
    use App\Services\Procurement\PurchaseOrders\PurchaseOrderBuyerCompanyApplier;
    use App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels;
    use App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext;

    $printLabels = $printLabels ?? PurchaseOrderPrintLabels::resolve(null);
    $buyer = $buyerCompany ?? \App\Enums\Procurement\BuyerCompany::forDisplay($purchaseOrder);
    $prContext = $prContext ?? PurchaseOrderProcurementRequestContext::resolve($purchaseOrder, $printLabels);
    $poCompany = PurchaseOrderBuyerCompanyApplier::resolveForPurchaseOrder($purchaseOrder);
    $minItemRows = 1;
    $poLogoUrl = $poCompany->logoUrl();
    $poLogoExists = $poCompany->logoExists();
    $poLogoFallbackHtml = $poCompany->logoFallbackHtml();
    $termsLocale = $printLabels->locale();
    $withTerms = $withTerms ?? true;
@endphp

@include('procurement.purchase-orders.print._page-setup', ['buyer' => $buyer])

<div class="po-wrapper">
    @include('procurement.purchase-orders.print._header', [
        'buyer' => $buyer,
        'poCompany' => $poCompany,
        'poLogoUrl' => $poLogoUrl,
        'poLogoExists' => $poLogoExists,
        'poLogoFallbackHtml' => $poLogoFallbackHtml,
        'prContext' => $prContext,
    ])
    @include('procurement.purchase-orders.print._parties')
    @include('procurement.purchase-orders.print._items', [
        'minItemRows' => $minItemRows,
        'prContext' => $prContext,
    ])
    @include('procurement.purchase-orders.print._supporting-documents', ['prContext' => $prContext])
    @include('procurement.purchase-orders.print._terms', ['termsLocale' => $termsLocale, 'withTerms' => $withTerms])
    @include('procurement.purchase-orders.print._signatures')
</div>

@include('procurement.purchase-orders.print._footer', ['buyer' => $buyer])
