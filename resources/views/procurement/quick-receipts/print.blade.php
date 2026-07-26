@extends('layouts.print')

@section('title', 'إيصال '.$receipt->code)

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
            <strong style="font-family:monospace;">{{ $receipt->code }}</strong> — معاينة الطباعة
        </p>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:16px;">
            <button type="button" onclick="window.print()"
                    style="padding:8px 16px;background:#0f172a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">
                طباعة
            </button>
            <a href="{{ route('quick-receipts.show', $receipt) }}"
               style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;border-radius:6px;font-size:13px;text-decoration:none;">
                العودة للإيصال
            </a>
        </div>
    </div>

    <div class="inv-print-page inv-print--rtl" dir="rtl" lang="ar">
        @include('procurement.quick-receipts.print._document')
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
