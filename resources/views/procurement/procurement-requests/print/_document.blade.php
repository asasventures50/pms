@php
    use App\Enums\Procurement\BuyerCompany;
    use Illuminate\Support\Facades\Storage;

    $buyer = $buyerCompany ?? BuyerCompany::forDisplay();
    $minItemRows = 1;
    $poLogoPublicPath = public_path('images/po/logo.png');
    $poLogoExists = is_file($poLogoPublicPath)
        || Storage::disk('public')->exists('logo.png');
    $poLogoUrl = is_file($poLogoPublicPath)
        ? asset('images/po/logo.png')
        : (Storage::disk('public')->exists('logo.png') ? Storage::disk('public')->url('logo.png') : asset('images/po/logo.png'));
@endphp

@include('procurement.procurement-requests.print._page-setup', ['buyer' => $buyer])

<div class="po-wrapper">
    @include('procurement.procurement-requests.print._header', [
        'buyer' => $buyer,
        'poLogoUrl' => $poLogoUrl,
        'poLogoExists' => $poLogoExists,
    ])
    @include('procurement.procurement-requests.print._request-info')
    @include('procurement.procurement-requests.print._items', [
        'minItemRows' => $minItemRows,
    ])
    @include('procurement.procurement-requests.print._signatures')
</div>

@include('procurement.procurement-requests.print._footer', ['buyer' => $buyer])
