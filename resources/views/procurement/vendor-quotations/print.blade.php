@extends('layouts.print')

@section('title', $quotation->quotation_number)

@push('styles')
    @include('procurement.vendor-quotations.print._styles')
@endpush

@section('content')
    <div class="print-toolbar vq-print-page">
        <p style="margin:0 0 12px;font-size:13px;color:#475569;">
            <strong style="font-family:monospace;">{{ $quotation->quotation_number }}</strong> — Vendor quotation print preview
        </p>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:16px;">
            <button type="button" onclick="window.print()"
                    style="padding:8px 16px;background:#0f172a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">
                Print
            </button>
            <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}"
               style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;border-radius:6px;font-size:13px;text-decoration:none;">
                Back to quotation
            </a>
        </div>
    </div>

    <div class="vq-print-page">
        <article class="vq-document">
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

@push('scripts')
    <script>
        window.addEventListener('load', function () {
            if (!window.matchMedia('print').matches) {
                window.print();
            }
        });
    </script>
@endpush
