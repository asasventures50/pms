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
    <div class="inv-header-title">فاتورة</div>
</div>

<div class="inv-meta-simple">
    <div class="inv-meta-row">
        <span class="inv-meta-label">رقم الفاتورة</span>
        <span class="inv-meta-value inv-ltr">{{ $invoice->invoice_number }}</span>
    </div>
    <div class="inv-meta-row">
        <span class="inv-meta-label">التاريخ</span>
        <span class="inv-meta-value">{{ $invoice->invoiced_at?->format('d-m-Y') }}</span>
    </div>
</div>
