@extends('layouts.print')

@section('title', 'PR '.$procurementRequest->request_number)

@push('styles')
    @include('procurement.procurement-requests.print._styles')
    <style>
        .po-print-page {
            max-width: 210mm;
            margin: 0 auto;
            padding: 16px;
            box-sizing: border-box;
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
            <strong style="font-family:monospace;">{{ $procurementRequest->request_number }}</strong> — print preview
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
            <button type="button" onclick="window.print()"
                    style="padding:8px 16px;background:#0f172a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">
                Print
            </button>
            <a href="{{ route('procurement-requests.show', $procurementRequest) }}"
               style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;border-radius:6px;font-size:13px;text-decoration:none;">
                Back to PR
            </a>
        </div>
    </div>

    <div class="po-print-page">
        @include('procurement.procurement-requests.print._document')
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
