@extends('layouts.print')

@section('title', 'RFQ general terms')

@section('content')
    @php
        $filters = $filters ?? ['q' => '', 'scope_type' => '', 'is_active' => '1', 'locale' => 'both'];
        $showAr = in_array($filters['locale'], ['both', 'ar'], true);
        $showEn = in_array($filters['locale'], ['both', 'en'], true);
        $totalTerms = collect($sections ?? [])->sum(fn ($section) => $section['terms']->count());
    @endphp

    <div class="rfq-terms-print">
        <div class="rfq-terms-print__toolbar no-print">
            <h1 class="rfq-terms-print__title">RFQ general terms — print</h1>
            <p class="rfq-terms-print__summary">{{ $filterSummary ?? '' }}</p>

            <form method="get" action="{{ route('rfq-terms.print') }}" class="rfq-terms-print__filters">
                <div class="rfq-terms-print__filters-grid">
                    <div>
                        <label for="scope_type" class="rfq-terms-print__label">Scope</label>
                        <select name="scope_type" id="scope_type" class="rfq-terms-print__control">
                            <option value="" @selected(($filters['scope_type'] ?? '') === '')>All (grouped by scope)</option>
                            <option value="global" @selected(($filters['scope_type'] ?? '') === 'global')>General (all RFQs) only</option>
                            @foreach ($scopeTypes as $scopeType)
                                <option value="{{ $scopeType }}" @selected(($filters['scope_type'] ?? '') === $scopeType)>{{ $scopeType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="locale" class="rfq-terms-print__label">Language</label>
                        <select name="locale" id="locale" class="rfq-terms-print__control">
                            <option value="both" @selected(($filters['locale'] ?? 'both') === 'both')>Arabic &amp; English</option>
                            <option value="ar" @selected(($filters['locale'] ?? '') === 'ar')>Arabic only</option>
                            <option value="en" @selected(($filters['locale'] ?? '') === 'en')>English only</option>
                        </select>
                    </div>
                    <div>
                        <label for="is_active" class="rfq-terms-print__label">Status</label>
                        <select name="is_active" id="is_active" class="rfq-terms-print__control">
                            <option value="1" @selected(($filters['is_active'] ?? '1') === '1')>Active only</option>
                            <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive only</option>
                            <option value="" @selected(($filters['is_active'] ?? '1') === '')>All</option>
                        </select>
                    </div>
                    <div>
                        <label for="q" class="rfq-terms-print__label">Search</label>
                        <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Arabic or English text"
                               class="rfq-terms-print__control">
                    </div>
                </div>
                <div class="rfq-terms-print__actions">
                    <button type="submit" class="rfq-terms-print__btn rfq-terms-print__btn--secondary">Apply filters</button>
                    <button type="button" onclick="window.print()" class="rfq-terms-print__btn rfq-terms-print__btn--primary">Print</button>
                    <a href="{{ route('rfq-terms.index') }}" class="rfq-terms-print__btn rfq-terms-print__btn--link">Back to list</a>
                </div>
            </form>
        </div>

        <header class="rfq-terms-print__header print-only-header">
            <h1 class="rfq-terms-print__doc-title">RFQ general terms</h1>
            <p class="rfq-terms-print__doc-meta">{{ $filterSummary ?? '' }}</p>
            <p class="rfq-terms-print__doc-meta">Printed {{ now()->format('d-m-Y H:i') }} · {{ $totalTerms }} {{ str('term')->plural($totalTerms) }}</p>
        </header>

        @forelse ($sections as $section)
            <section class="rfq-terms-print__section">
                <h2 class="rfq-terms-print__section-title">{{ $section['label'] }}</h2>
                <ol class="rfq-terms-print__list">
                    @foreach ($section['terms'] as $term)
                        <li class="rfq-terms-print__item">
                            <span class="rfq-terms-print__order">{{ $term->sort_order }}</span>
                            <div class="rfq-terms-print__body">
                                @if ($showAr && $term->body_ar)
                                    <p class="rfq-terms-print__text rfq-terms-print__text--ar" dir="rtl" lang="ar">{{ $term->body_ar }}</p>
                                @endif
                                @if ($showEn && $term->body_en)
                                    <p @class([
                                        'rfq-terms-print__text',
                                        'rfq-terms-print__text--en',
                                        'rfq-terms-print__text--secondary' => $showAr && $term->body_ar,
                                    ])>{{ $term->body_en }}</p>
                                @endif
                                @if ((! $showAr || ! $term->body_ar) && (! $showEn || ! $term->body_en))
                                    <p class="rfq-terms-print__text rfq-terms-print__text--empty">—</p>
                                @endif
                                @if (! $term->is_active)
                                    <span class="rfq-terms-print__inactive">Inactive</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @empty
            <p class="rfq-terms-print__empty">No terms match the selected filters.</p>
        @endforelse
    </div>
@endsection

@push('styles')
    <style>
        .rfq-terms-print {
            max-width: 210mm;
            margin: 0 auto;
            padding: 16px;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #0f172a;
            box-sizing: border-box;
        }

        .rfq-terms-print__toolbar {
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .rfq-terms-print__title {
            margin: 0 0 4px;
            font-size: 20px;
            font-weight: 600;
        }

        .rfq-terms-print__summary {
            margin: 0 0 16px;
            font-size: 13px;
            color: #475569;
        }

        .rfq-terms-print__filters-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        }

        .rfq-terms-print__label {
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }

        .rfq-terms-print__control {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 13px;
            box-sizing: border-box;
        }

        .rfq-terms-print__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .rfq-terms-print__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .rfq-terms-print__btn--primary {
            background: #0f172a;
            color: #fff;
        }

        .rfq-terms-print__btn--secondary {
            background: #fff;
            color: #1e293b;
            border: 1px solid #cbd5e1;
        }

        .rfq-terms-print__btn--link {
            background: transparent;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .rfq-terms-print__header {
            display: none;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0f172a;
        }

        .rfq-terms-print__doc-title {
            margin: 0 0 6px;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .rfq-terms-print__doc-meta {
            margin: 0 0 4px;
            font-size: 11px;
            color: #475569;
        }

        .rfq-terms-print__section {
            margin-bottom: 24px;
        }

        .rfq-terms-print__section-title {
            margin: 0 0 10px;
            padding-bottom: 4px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #334155;
            border-bottom: 1px solid #cbd5e1;
        }

        .rfq-terms-print__list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .rfq-terms-print__item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .rfq-terms-print__order {
            flex-shrink: 0;
            width: 2.5rem;
            font-family: ui-monospace, monospace;
            font-size: 11px;
            color: #64748b;
            text-align: right;
        }

        .rfq-terms-print__body {
            flex: 1;
            min-width: 0;
        }

        .rfq-terms-print__text {
            margin: 0;
            line-height: 1.45;
        }

        .rfq-terms-print__text + .rfq-terms-print__text {
            margin-top: 4px;
        }

        .rfq-terms-print__text--secondary {
            color: #475569;
            font-size: 12px;
        }

        .rfq-terms-print__text--empty {
            color: #94a3b8;
        }

        .rfq-terms-print__inactive {
            display: inline-block;
            margin-top: 4px;
            padding: 1px 6px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: #b45309;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 4px;
        }

        .rfq-terms-print__empty {
            padding: 24px;
            text-align: center;
            color: #64748b;
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

            .no-print,
            .rfq-terms-print__toolbar {
                display: none !important;
                height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }

            .print-only-header {
                display: block !important;
            }

            .rfq-terms-print {
                max-width: none;
                padding: 0;
            }

            .rfq-terms-print__section-title {
                break-after: avoid;
                page-break-after: avoid;
            }

            .rfq-terms-print__item {
                break-inside: auto;
                page-break-inside: auto;
            }
        }
    </style>
@endpush
