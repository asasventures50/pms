@php
    use App\Enums\Procurement\PrCompany;
    use App\Enums\Procurement\Rfqs\RfqTermsLocale;

    $company = PrCompany::AsasVentures;
    $buyer = $company->details();
    $logoUrl = $company->logoUrl();
    $logoExists = $company->logoExists();
    $printLabels = $printLabels ?? \App\Services\Procurement\ScheduleOfWorks\ScheduleOfWorkPrintLabels::resolve($schedule->print_locale);
    $isRtl = $printLabels->isRtl();
@endphp

@extends('layouts.print')

@section('title', $printLabels->t('document_title').' '.$schedule->document_number)

@section('html_lang')
    {{ $printLabels->locale() }}
@endsection

@push('styles')
    @include('procurement.invoices.print._styles')
    @include('procurement.purchase-orders.print._styles')
    <style>
        .inv-sow-terms-block {
            margin-bottom: 12px;
        }
    </style>
    @unless ($isRtl)
        <style>
            .inv-print-page:not(.inv-print--rtl) .inv-cell-text,
            .inv-print-page:not(.inv-print--rtl) .inv-cell-project,
            .inv-print-page:not(.inv-print--rtl) .inv-fee-label {
                text-align: left;
            }
            .inv-print-page:not(.inv-print--rtl) .inv-notes-list {
                padding: 0 0 0 20px;
            }
            .inv-print-page:not(.inv-print--rtl) .inv-bank-block {
                text-align: left;
            }
        </style>
    @endunless
@endpush

@section('content')
    <div class="print-toolbar inv-print-page">
        <p style="margin:0 0 12px;font-size:13px;color:#475569;">
            <strong style="font-family:monospace;">{{ $schedule->document_number }}</strong> — {{ $printLabels->t('print_preview') }}
        </p>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:16px;">
            <span style="font-size:13px;color:#475569;">{{ $printLabels->t('language') }}:</span>
            @foreach (RfqTermsLocale::cases() as $locale)
                <a href="{{ route('schedule-of-works.print', ['schedule_of_work' => $schedule, 'locale' => $locale->value]) }}"
                   style="padding:6px 12px;border:1px solid {{ $printLabels->locale() === $locale->value ? '#0f172a' : '#cbd5e1' }};background:{{ $printLabels->locale() === $locale->value ? '#0f172a' : '#fff' }};color:{{ $printLabels->locale() === $locale->value ? '#fff' : '#1e293b' }};border-radius:6px;font-size:12px;text-decoration:none;">
                    {{ $locale->label() }}
                </a>
            @endforeach
            <button type="button" onclick="window.print()"
                    style="padding:8px 16px;background:#0f172a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;margin-inline-start:8px;">
                {{ $printLabels->t('print') }}
            </button>
            <a href="{{ route('schedule-of-works.index') }}"
               style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#1e293b;border-radius:6px;font-size:13px;text-decoration:none;">
                {{ $printLabels->t('back') }}
            </a>
        </div>
    </div>

    <div class="inv-print-page {{ $isRtl ? 'inv-print--rtl' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" lang="{{ $printLabels->locale() }}">
        @include('procurement.schedule-of-works.print._document', [
            'company' => $company,
            'buyer' => $buyer,
            'logoUrl' => $logoUrl,
            'logoExists' => $logoExists,
            'schedule' => $schedule,
            'printLabels' => $printLabels,
            'terms' => $terms ?? [],
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
