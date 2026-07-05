@php
    $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);
    $withTerms = $withTerms ?? true;
@endphp

@extends('layouts.print')

@section('title', 'PO '.$purchaseOrder->po_number)

@section('html_lang')
    {{ $printLabels->locale() }}
@endsection

@push('styles')
    @include('procurement.purchase-orders.print._styles')
    <style>
        .po-print-page {
            max-width: 210mm;
            margin: 0 auto;
            padding: 16px;
            box-sizing: border-box;
        }

        .po-print--rtl {
            direction: rtl;
            text-align: right;
        }

        .po-print--rtl .po-header-title,
        .po-print--rtl .po-header-dept,
        .po-print--rtl .po-section-title,
        .po-print--rtl .po-form-label,
        .po-print--rtl .po-field-label {
            text-align: right;
        }

        .po-print--rtl .po-items-table th,
        .po-print--rtl .po-items-table td {
            text-align: right;
        }

        .po-print--rtl .po-cell-num {
            text-align: left;
        }

        .po-print--rtl .po-order-right {
            text-align: right;
        }

        .po-print--rtl .po-order-right .po-form-label {
            width: 88px;
            text-align: right;
            margin-inline-end: 4px;
        }

        .po-print--rtl .po-order-right .po-form-line {
            width: 200px;
            max-width: 200px;
            text-align: right;
        }

        .po-print--rtl .po-signature-col {
            text-align: right;
        }

        .po-print--rtl .po-signature-row .po-form-label {
            width: auto;
            text-align: right;
            margin-inline-end: 4px;
        }

        .po-print--rtl .po-signature-row .po-form-line {
            width: 180px;
            max-width: 180px;
            text-align: right;
        }

        @media print {
            .po-print-page {
                max-width: none;
                padding: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="print-toolbar po-print-page">
        <p style="margin:0 0 12px;font-size:13px;color:#475569;">
            <strong style="font-family:monospace;">{{ $purchaseOrder->po_number }}</strong> — {{ $printLabels->t('print_preview') }}
        </p>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:16px;">
            <button type="button" onclick="window.print()"
                    style="padding:8px 16px;background:#0f172a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">
                {{ $printLabels->t('print') }}
            </button>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
               style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;border-radius:6px;font-size:13px;text-decoration:none;">
                {{ $printLabels->t('back_to_po') }}
            </a>
        </div>
    </div>

    <div @class(['po-print-page', 'po-print--rtl' => $printLabels->isRtl()]) @if ($printLabels->isRtl()) dir="rtl" lang="ar" @endif>
        @include('procurement.purchase-orders.print._document')
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
