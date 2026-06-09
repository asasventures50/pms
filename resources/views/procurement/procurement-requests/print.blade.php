@php
    use App\Enums\Procurement\Rfqs\RfqTermsLocale;

    $printLabels = $printLabels ?? \App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels::resolve(null);
@endphp

@extends('layouts.print')

@section('title', 'PR '.$procurementRequest->request_number)

@section('html_lang')
    {{ $printLabels->locale() }}
@endsection

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
            <strong style="font-family:monospace;">{{ $procurementRequest->request_number }}</strong> — {{ $printLabels->t('print_preview') }}
        </p>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:16px;">
            <span style="font-size:13px;color:#475569;">{{ $printLabels->t('language') }}:</span>
            @foreach (RfqTermsLocale::cases() as $locale)
                <a href="{{ route('procurement-requests.print', ['procurement_request' => $procurementRequest, 'locale' => $locale->value]) }}"
                   style="padding:8px 12px;border:1px solid {{ $printLabels->locale() === $locale->value ? '#0f172a' : '#cbd5e1' }};background:{{ $printLabels->locale() === $locale->value ? '#0f172a' : '#fff' }};color:{{ $printLabels->locale() === $locale->value ? '#fff' : '#1e293b' }};border-radius:6px;font-size:13px;text-decoration:none;">
                    {{ $locale->label() }}
                </a>
            @endforeach
            <button type="button" onclick="window.print()"
                    style="padding:8px 16px;background:#0f172a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">
                {{ $printLabels->t('print') }}
            </button>
            <a href="{{ route('procurement-requests.show', $procurementRequest) }}"
               style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;border-radius:6px;font-size:13px;text-decoration:none;">
                {{ $printLabels->t('back_to_pr') }}
            </a>
        </div>
    </div>

    <div @class(['po-print-page', 'pr-print--rtl' => $printLabels->isRtl()]) @if ($printLabels->isRtl()) dir="rtl" lang="ar" @endif>
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
