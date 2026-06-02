@php
    use App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext;
    use Illuminate\Support\Facades\Storage;

    $buyer = $buyerCompany ?? \App\Enums\Procurement\BuyerCompany::forDisplay($purchaseOrder);
    $prContext = $prContext ?? PurchaseOrderProcurementRequestContext::resolve($purchaseOrder);
    $minItemRows = 1;
    $poLogoPublicPath = public_path('images/po/logo.png');
    $poLogoExists = is_file($poLogoPublicPath)
        || Storage::disk('public')->exists('logo.png');
    $poLogoUrl = is_file($poLogoPublicPath)
        ? asset('images/po/logo.png')
        : (Storage::disk('public')->exists('logo.png') ? Storage::disk('public')->url('logo.png') : asset('images/po/logo.png'));
    $termsLocale = $purchaseOrder->terms_locale ?? 'en';
@endphp

@include('procurement.purchase-orders.print._page-setup', ['buyer' => $buyer])

<div class="po-wrapper">
    @include('procurement.purchase-orders.print._header', [
        'buyer' => $buyer,
        'poLogoUrl' => $poLogoUrl,
        'poLogoExists' => $poLogoExists,
        'prContext' => $prContext,
    ])
    @include('procurement.purchase-orders.print._parties')
    @include('procurement.purchase-orders.print._items', [
        'minItemRows' => $minItemRows,
        'prContext' => $prContext,
    ])
    @include('procurement.purchase-orders.print._terms', ['termsLocale' => $termsLocale])
    @include('procurement.purchase-orders.print._signatures')
</div>

@include('procurement.purchase-orders.print._footer', ['buyer' => $buyer])
