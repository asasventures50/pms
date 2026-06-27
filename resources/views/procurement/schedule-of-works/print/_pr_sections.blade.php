@php
    $sections = $schedule->displayPrSections();
    $yesNo = static function (?bool $value) use ($printLabels): string {
        if ($value === null) {
            return '';
        }
        return $value ? ($printLabels->locale() === 'ar' ? 'نعم' : 'Yes') : ($printLabels->locale() === 'ar' ? 'لا' : 'No');
    };
@endphp

@if ($sections !== [])
    <div class="inv-notes-block sow-pr-sections-print">
        @if (! empty($sections['pr_info']))
            @php $info = $sections['pr_info']; @endphp
            <div class="inv-notes-title">{{ $printLabels->t('pr_information') }}</div>
            <dl class="inv-meta-simple" style="margin-bottom:12px;">
                @foreach ([
                    'project' => $printLabels->t('project'),
                    'zone' => $printLabels->t('zone'),
                    'category' => $printLabels->t('category'),
                    'subcategory' => $printLabels->t('subcategory'),
                    'procurement_type' => $printLabels->t('procurement_type'),
                    'geographic_scope' => $printLabels->t('geographic_scope'),
                    'vendor_type' => $printLabels->t('vendor_type'),
                ] as $key => $label)
                    @if (! empty($info[$key]))
                        <div class="inv-meta-row"><span class="inv-meta-label">{{ $label }}</span><span class="inv-meta-value">{{ $info[$key] }}</span></div>
                    @endif
                @endforeach
                @if (array_key_exists('samples_required', $info))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('samples_required') }}</span><span class="inv-meta-value">{{ $yesNo($info['samples_required']) }}</span></div>
                @endif
            </dl>
        @endif

        @if (! empty($sections['delivery']))
            @php $delivery = $sections['delivery']; @endphp
            <div class="inv-notes-title">{{ $printLabels->t('delivery_requirements') }}</div>
            <dl class="inv-meta-simple" style="margin-bottom:12px;">
                @if (! empty($delivery['lead_time_days']))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('lead_time_days') }}</span><span class="inv-meta-value">{{ $delivery['lead_time_days'] }}</span></div>
                @endif
                @if (! empty($delivery['location']))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('delivery_location') }}</span><span class="inv-meta-value">{{ $delivery['location'] }}</span></div>
                @endif
                @if (array_key_exists('flexible_delivery_date', $delivery))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('flexible_delivery') }}</span><span class="inv-meta-value">{{ $yesNo($delivery['flexible_delivery_date']) }}</span></div>
                @endif
            </dl>
        @endif

        @if (! empty($sections['supporting_documents']))
            <div class="inv-notes-title">{{ $printLabels->t('supporting_documents') }}</div>
            <ul class="inv-notes-list" style="margin-bottom:12px;">
                @foreach ($sections['supporting_documents'] as $doc)
                    <li>
                        @if (! empty($doc['file_url']))
                            <a href="{{ $doc['file_url'] }}">{{ $doc['file_name'] ?: $doc['file_url'] }}</a>
                        @else
                            {{ $doc['file_name'] ?? '—' }}
                        @endif
                        @if (! empty($doc['document_type'])) <span class="text-slate-500">({{ $doc['document_type'] }})</span> @endif
                        @if (! empty($doc['file_description'])) — {{ $doc['file_description'] }} @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if (! empty($sections['payment_terms']))
            <div class="inv-notes-title">{{ $printLabels->t('payment_terms') }}</div>
            <table class="inv-items-table" style="margin-bottom:12px;">
                <thead><tr><th>{{ $printLabels->t('milestone') }}</th><th>{{ $printLabels->t('note') }}</th><th>%</th><th>{{ $printLabels->t('due_upon') }}</th></tr></thead>
                <tbody>
                @foreach ($sections['payment_terms'] as $row)
                    <tr>
                        <td>{{ $row['milestone'] ?? '—' }}</td>
                        <td>{{ $row['amount'] ?? '—' }}</td>
                        <td>{{ $row['percentage'] ?? '—' }}</td>
                        <td>{{ $row['due_upon'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if (! empty($sections['retentions']))
            <div class="inv-notes-title">{{ $printLabels->t('retention') }}</div>
            <table class="inv-items-table" style="margin-bottom:12px;">
                <thead><tr><th>{{ $printLabels->t('retention_percent') }}</th><th>{{ $printLabels->t('release_period') }}</th></tr></thead>
                <tbody>
                @foreach ($sections['retentions'] as $row)
                    <tr><td>{{ $row['retention_percent'] ?? '—' }}</td><td>{{ $row['release_period'] ?? '—' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if (! empty($sections['maintenance']))
            @php $maint = $sections['maintenance']; @endphp
            <div class="inv-notes-title">{{ $printLabels->t('maintenance') }}</div>
            <dl class="inv-meta-simple" style="margin-bottom:12px;">
                @if (array_key_exists('after_sale_service_applicable', $maint))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('after_sale_service') }}</span><span class="inv-meta-value">{{ $yesNo($maint['after_sale_service_applicable']) }}</span></div>
                @endif
                @if (! empty($maint['warranty_years']))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('warranty_years') }}</span><span class="inv-meta-value">{{ $maint['warranty_years'] }}</span></div>
                @endif
                @if (! empty($maint['warranty_coverage']))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('warranty_coverage') }}</span><span class="inv-meta-value">{{ $maint['warranty_coverage'] }}</span></div>
                @endif
            </dl>
        @endif

        @if (! empty($sections['timeline']))
            <div class="inv-notes-title">{{ $printLabels->t('timeline') }}</div>
            <table class="inv-items-table" style="margin-bottom:12px;">
                <thead><tr><th>{{ $printLabels->t('activity') }}</th><th>{{ $printLabels->t('duration_days') }}</th></tr></thead>
                <tbody>
                @foreach ($sections['timeline'] as $row)
                    <tr><td>{{ $row['label'] ?? $row['activity'] ?? '—' }}</td><td>{{ $row['duration_days'] ?? '—' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if (! empty($sections['compliance']))
            @php $comp = $sections['compliance']; @endphp
            <div class="inv-notes-title">{{ $printLabels->t('compliance') }}</div>
            <dl class="inv-meta-simple">
                @foreach ([
                    'verification_required' => $printLabels->t('verification_required'),
                    'prequalification_required' => $printLabels->t('prequalification_required'),
                    'nda_required' => $printLabels->t('nda_required'),
                    'conflict_of_interest_required' => $printLabels->t('conflict_of_interest'),
                    'commitment_compliance_required' => $printLabels->t('commitment_compliance'),
                ] as $key => $label)
                    @if (array_key_exists($key, $comp))
                        <div class="inv-meta-row"><span class="inv-meta-label">{{ $label }}</span><span class="inv-meta-value">{{ $yesNo($comp[$key]) }}</span></div>
                    @endif
                @endforeach
                @if (! empty($comp['prequalification_level']))
                    <div class="inv-meta-row"><span class="inv-meta-label">{{ $printLabels->t('prequalification_level') }}</span><span class="inv-meta-value">{{ $comp['prequalification_level'] }}</span></div>
                @endif
            </dl>
        @endif
    </div>
@endif
