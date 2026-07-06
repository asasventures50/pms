@php
    use App\Enums\Procurement\PrCompany;

    $company = PrCompany::AsasVentures;
    $buyer = $company->details();
    $logoUrl = $company->logoUrl();
    $logoExists = $company->logoExists();
@endphp

@extends('layouts.print')

@section('title', 'فاتورة '.$invoice->invoice_number)

@section('html_lang')
    ar
@endsection

@push('styles')
    @include('procurement.invoices.print._styles')
@endpush

@include('print._page-numbers')

@section('content')
    <div class="print-toolbar inv-print-page">
        <p style="margin:0 0 12px;font-size:13px;color:#475569;">
            <strong style="font-family:monospace;">{{ $invoice->invoice_number }}</strong> — معاينة الطباعة
        </p>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:16px;">
            <button type="button" onclick="window.print()"
                    style="padding:8px 16px;background:#0f172a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">
                طباعة
            </button>
            <a href="{{ route('invoices.index') }}"
               style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;border-radius:6px;font-size:13px;text-decoration:none;">
                العودة للفواتير
            </a>
        </div>
    </div>

    <div class="inv-print-page inv-print--rtl" dir="rtl" lang="ar">
        @include('procurement.invoices.print._document', [
            'company' => $company,
            'buyer' => $buyer,
            'logoUrl' => $logoUrl,
            'logoExists' => $logoExists,
        ])
    </div>
@endsection

@push('scripts')
    <script>
        window.addEventListener('load', function () {
            if (!window.matchMedia('print').matches) {
                window.print();
            }
        });
    </script>
@endpush
