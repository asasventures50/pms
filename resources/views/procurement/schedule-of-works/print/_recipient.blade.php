<div class="inv-recipient-block">
    <span class="inv-recipient-label">{{ $printLabels->t('recipient_label') }}</span>
    <span class="inv-recipient-name">{{ $schedule->recipient_name }}</span>
</div>

@if (filled($schedule->vendor_company_name))
    <div class="inv-meta-simple" style="margin-bottom:12px;">
        <div class="inv-meta-row">
            <span class="inv-meta-label">{{ $printLabels->t('vendor') }}</span>
            <span class="inv-meta-value">{{ $schedule->vendor_company_name }}</span>
        </div>
    </div>
@endif
