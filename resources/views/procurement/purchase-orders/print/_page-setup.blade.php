@php
    $footerCompanyName = strtoupper($buyer['name'] ?? $buyerCompany['name'] ?? \App\Enums\Procurement\BuyerCompany::NAME ?? 'ASAS VENTURES');
@endphp

@push('styles')
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 14mm 24mm 14mm;
            background: #fff;

            @bottom-left {
                content: "Form PO";
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-weight: bold;
                vertical-align: top;
                padding-top: 1.5mm;
                padding-left: 14mm;
                border-top: 2px solid #000;
            }

            @bottom-center {
                content: {!! json_encode($footerCompanyName) !!};
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-weight: bold;
                text-align: center;
                vertical-align: top;
                padding-top: 1.5mm;
                border-top: 2px solid #000;
            }

            @bottom-right {
                content: counter(page) "/" counter(pages);
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                font-weight: bold;
                text-align: right;
                vertical-align: bottom;
                padding-right: 14mm;
                padding-bottom: 1mm;
            }
        }
    </style>
@endpush
