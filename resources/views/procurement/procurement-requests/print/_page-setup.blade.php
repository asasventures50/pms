@php
    use App\Support\Procurement\ProcurementRequestPrintPageCss;

    $footerCompanyName = $buyer['name'] ?? $buyerCompany['name'] ?? \App\Enums\Procurement\BuyerCompany::NAME ?? 'ASAS VENTURES';
@endphp

@push('styles')
    {!! ProcurementRequestPrintPageCss::styleTag($footerCompanyName) !!}
@endpush
