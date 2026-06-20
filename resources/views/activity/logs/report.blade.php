@extends('layouts.print')

@section('title', 'Activity report')

@section('content')
    <div class="activity-report">
        <div class="activity-report__toolbar no-print">
            <h1 class="activity-report__title">Activity report</h1>
            <p class="activity-report__summary">{{ $filterSummary }}</p>
            <p class="activity-report__meta">{{ $totalEvents }} {{ str('event')->plural($totalEvents) }} in this report</p>

            <form method="get" action="{{ route('activity-logs.report') }}" class="activity-report__filters">
                <div class="activity-report__filters-grid">
                    <div>
                        <label for="user" class="activity-report__label">User</label>
                        <select id="user" name="user" class="activity-report__control">
                            <option value="">All users</option>
                            @foreach ($users as $userOption)
                                <option value="{{ $userOption->id }}" @selected(($filters['user'] ?? '') === (string) $userOption->id)>
                                    {{ $userOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="action" class="activity-report__label">Action</label>
                        <select id="action" name="action" class="activity-report__control">
                            <option value="">All actions</option>
                            @foreach ($actions as $actionOption)
                                <option value="{{ $actionOption }}" @selected(($filters['action'] ?? '') === $actionOption)>{{ $actionOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="activity-report__label">From date</label>
                        <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="activity-report__control">
                    </div>
                    <div>
                        <label for="date_to" class="activity-report__label">To date</label>
                        <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="activity-report__control">
                    </div>
                    <div class="activity-report__filters-wide">
                        <label for="q" class="activity-report__label">Search</label>
                        <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Description, action, or IP"
                               class="activity-report__control">
                    </div>
                </div>
                <div class="activity-report__actions">
                    <button type="submit" class="activity-report__btn activity-report__btn--secondary">Apply filters</button>
                    <button type="button" onclick="window.print()" class="activity-report__btn activity-report__btn--primary">Print / Save as PDF</button>
                    <a href="{{ route('activity-logs.index', $filters) }}" class="activity-report__btn activity-report__btn--link">Back to log</a>
                </div>
            </form>
        </div>

        <header class="activity-report__header print-only-header">
            <h1 class="activity-report__doc-title">Activity report</h1>
            <p class="activity-report__doc-meta">{{ $filterSummary }}</p>
            <p class="activity-report__doc-meta">
                Printed {{ now()->format('d-m-Y H:i') }} · {{ $totalEvents }} {{ str('event')->plural($totalEvents) }}
            </p>
        </header>

        @forelse ($userReports as $userReport)
            <section class="activity-report__section">
                <h2 class="activity-report__section-title">
                    {{ $userReport['user_name'] }}
                    @if ($userReport['user_email'])
                        <span class="activity-report__section-email">{{ $userReport['user_email'] }}</span>
                    @endif
                </h2>

                @if ($userReport['summaries'] !== [])
                    <h3 class="activity-report__subsection-title">Summary</h3>
                    <ul class="activity-report__summary-list">
                        @foreach ($userReport['summaries'] as $summary)
                            <li>{{ $summary['label'] }}</li>
                        @endforeach
                    </ul>
                @endif

                @if ($userReport['timeline'] !== [])
                    <h3 class="activity-report__subsection-title">Timeline</h3>
                    <ol class="activity-report__timeline">
                        @foreach ($userReport['timeline'] as $entry)
                            <li>
                                <span class="activity-report__timeline-when">{{ $entry['when'] }}</span>
                                <span class="activity-report__timeline-label">{{ $entry['label'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        @empty
            <p class="activity-report__empty">No activity matches the selected filters.</p>
        @endforelse
    </div>

    @push('styles')
        <style>
            .activity-report {
                max-width: 920px;
                margin: 0 auto;
                padding: 24px;
                color: #0f172a;
                font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
                line-height: 1.5;
            }

            .activity-report__toolbar {
                margin-bottom: 32px;
                padding: 20px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background: #f8fafc;
            }

            .activity-report__title,
            .activity-report__doc-title {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 700;
            }

            .activity-report__summary,
            .activity-report__doc-meta,
            .activity-report__meta {
                margin: 8px 0 0;
                color: #475569;
                font-size: 0.95rem;
            }

            .activity-report__filters {
                margin-top: 20px;
            }

            .activity-report__filters-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .activity-report__filters-wide {
                grid-column: 1 / -1;
            }

            .activity-report__label {
                display: block;
                margin-bottom: 4px;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: #64748b;
            }

            .activity-report__control {
                width: 100%;
                padding: 8px 10px;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                background: #fff;
                font-size: 0.95rem;
            }

            .activity-report__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 16px;
            }

            .activity-report__btn {
                display: inline-flex;
                align-items: center;
                padding: 8px 14px;
                border-radius: 8px;
                font-size: 0.9rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
            }

            .activity-report__btn--primary {
                border: 1px solid #0f172a;
                background: #0f172a;
                color: #fff;
            }

            .activity-report__btn--secondary {
                border: 1px solid #cbd5e1;
                background: #fff;
                color: #0f172a;
            }

            .activity-report__btn--link {
                color: #334155;
            }

            .activity-report__header {
                display: none;
                margin-bottom: 24px;
                padding-bottom: 16px;
                border-bottom: 2px solid #0f172a;
            }

            .activity-report__section {
                margin-bottom: 28px;
                page-break-inside: avoid;
            }

            .activity-report__section-title {
                margin: 0 0 12px;
                font-size: 1.15rem;
                font-weight: 700;
            }

            .activity-report__section-email {
                display: block;
                margin-top: 2px;
                font-size: 0.85rem;
                font-weight: 500;
                color: #64748b;
            }

            .activity-report__subsection-title {
                margin: 16px 0 8px;
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: #64748b;
            }

            .activity-report__summary-list {
                margin: 0;
                padding-left: 1.25rem;
            }

            .activity-report__summary-list li {
                margin-bottom: 4px;
            }

            .activity-report__timeline {
                margin: 0;
                padding-left: 1.25rem;
            }

            .activity-report__timeline li {
                margin-bottom: 6px;
            }

            .activity-report__timeline-when {
                display: inline-block;
                min-width: 8.5rem;
                margin-right: 8px;
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 0.82rem;
                color: #64748b;
            }

            .activity-report__empty {
                padding: 24px;
                border: 1px dashed #cbd5e1;
                border-radius: 12px;
                text-align: center;
                color: #64748b;
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                .print-only-header {
                    display: block !important;
                }

                .activity-report {
                    max-width: none;
                    padding: 0;
                }

                .activity-report__section {
                    break-inside: avoid;
                }
            }
        </style>
    @endpush
@endsection
