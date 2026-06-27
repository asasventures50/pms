<div class="inv-header">
    <div class="inv-header-logo">
        <img src="{{ $logoUrl }}" alt="{{ $company->label() }}" class="inv-logo-img"
             @unless ($logoExists)
                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='block';"
             @endunless>
        <div class="inv-logo-fallback" @if ($logoExists) style="display:none;" @endif>
            {!! $company->logoFallbackHtml() !!}
        </div>
    </div>
    <div class="inv-header-title">{{ $printLabels->t('document_title') }}</div>
</div>

<div class="inv-meta-simple">
    <div class="inv-meta-row">
        <span class="inv-meta-label">{{ $printLabels->t('document_number') }}</span>
        <span class="inv-meta-value inv-ltr">{{ $schedule->document_number }}</span>
    </div>
    <div class="inv-meta-row">
        <span class="inv-meta-label">{{ $printLabels->t('date') }}</span>
        <span class="inv-meta-value">{{ $schedule->documented_at?->format('d-m-Y') }}</span>
    </div>
</div>

@php
    $scopeDisplay = $schedule->scopeTypesDisplay($printLabels->isRtl());
@endphp
@if ($scopeDisplay !== '')
    <div class="inv-recipient-block" style="margin-bottom:12px;">
        <span class="inv-recipient-label">{{ $printLabels->t('scope_of_work') }}:</span>
        <span>{{ $scopeDisplay }}</span>
    </div>
@endif
