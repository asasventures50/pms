@php
    use App\Support\Procurement\PurchaseOrderPrintPageCss;

    $footerCompanyName = $buyer['name'] ?? $buyerCompany['name'] ?? \App\Enums\Procurement\BuyerCompany::NAME ?? 'ASAS VENTURES';
@endphp

@push('styles')
    {!! PurchaseOrderPrintPageCss::styleTag($footerCompanyName) !!}
@endpush
