@php
    use App\Enums\Procurement\PrCompany;

    $prCompany = $prCompany ?? PrCompany::resolve($procurementRequest->company_key ?? null);
    $buyer = $buyerCompany ?? PrCompany::forDisplay($procurementRequest->company_key ?? null);
    $poLogoUrl = $prCompany->logoUrl();
    $poLogoExists = $prCompany->logoExists();
@endphp

@include('procurement.procurement-requests.print._page-setup', ['buyer' => $buyer])

<div class="po-wrapper pr-print-compact">
    @include('procurement.procurement-requests.print._header', [
        'buyer' => $buyer,
        'prCompany' => $prCompany,
        'poLogoUrl' => $poLogoUrl,
        'poLogoExists' => $poLogoExists,
    ])
    @include('procurement.procurement-requests.print._request-info', ['formData' => $formData ?? []])
    @include('procurement.procurement-requests.print._items', ['formData' => $formData ?? []])
    @include('procurement.procurement-requests.print._sections', ['formData' => $formData ?? []])
</div>

@include('procurement.procurement-requests.print._footer', ['buyer' => $buyer])
