@extends('layouts.print')

@section('title', $quotation->quotation_number)

@push('styles')
    @vite(['resources/css/app.css'])
    <style>
        body.po-print-body {
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .vq-print-page {
            max-width: 72rem;
            margin: 0 auto;
            padding: 16px;
            box-sizing: border-box;
        }

        @media print {
            @page {
                margin: 12mm;
            }

            html,
            body.po-print-body {
                height: auto;
                margin: 0;
                padding: 0;
                background: #fff;
            }

            .vq-print-page {
                max-width: none;
                padding: 0;
            }

            .print-toolbar {
                display: none !important;
            }

            .vq-document {
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="print-toolbar vq-print-page">
        <p class="mb-3 text-sm text-slate-600">
            <strong class="font-mono">{{ $quotation->quotation_number }}</strong> — Vendor quotation print preview
        </p>
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <button type="button" onclick="window.print()"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Print
            </button>
            <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Back to quotation
            </a>
        </div>
    </div>

    <div class="vq-print-page">
        <article class="vq-document mx-auto max-w-6xl border-2 border-slate-900 bg-white p-4 text-slate-900 shadow-sm sm:p-6 print:border print:shadow-none">
            @include('procurement.vendor-quotations._document-body', [
                'rfq' => $rfq,
                'quotation' => $quotation,
                'rfqContext' => $rfqContext,
                'buyerCompany' => $buyerCompany ?? null,
                'documentTypes' => $documentTypes,
                'declarations' => $declarations,
            ])
        </article>
    </div>
@endsection
