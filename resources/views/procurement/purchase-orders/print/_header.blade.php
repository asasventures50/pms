@php
    $printLabels = $printLabels ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels::resolve(null);
@endphp

<table class="po-header-table">
    <tr>
        <td class="po-header-logo">
            <img src="{{ $poLogoUrl }}" alt="{{ $poCompany->label() ?? 'Company' }}" class="po-logo-img"
                 @unless ($poLogoExists)
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='block';"
                 @endunless>
            <div class="po-logo-fallback" @if ($poLogoExists) style="display:none;" @endif>
                {!! $poLogoFallbackHtml ?? 'ASAS<br>VENTURES' !!}
            </div>
        </td>
        <td class="po-header-title">{{ $printLabels->t('document_title') }}</td>
        <td class="po-header-dept">{!! nl2br(e($printLabels->t('department'))) !!}</td>
    </tr>
</table>

<div class="po-company-info">
    <div class="po-company-name">{{ strtoupper($buyer['name'] ?? 'ASAS VENTURES') }}</div>
    @if ($buyer['address'] ?? null)
        <div>{{ $printLabels->t('address') }} {{ $buyer['address'] }}</div>
    @endif
    @if ($buyer['phone'] ?? null)
        <div>{{ $printLabels->t('phone') }} <span class="po-ltr">{{ $buyer['phone'] }}</span></div>
    @endif
    @if ($buyer['email'] ?? null)
        <div>{{ $printLabels->t('email') }} <span class="po-ltr">{{ $buyer['email'] }}</span></div>
    @endif
    @if ($buyer['fax'] ?? null)
        <div>{{ $printLabels->t('fax') }} <span class="po-ltr">{{ $buyer['fax'] }}</span></div>
    @endif
</div>

<div class="po-section-title">{{ $printLabels->t('order_information') }}</div>
<div class="po-grid-2">
    <div class="po-grid-col po-order-left">
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('po_number') }}</span>
            <span class="po-form-line">{{ $purchaseOrder->po_number }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('date') }}</span>
            <span class="po-form-line">{{ $purchaseOrder->ordered_at?->format('d-m-Y') ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('pr_number') }}</span>
            <span class="po-form-line">{{ $purchaseOrder->procurementRequest?->request_number ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('procurement_type') }}</span>
            <span class="po-form-line">{{ $prContext['procurement_type'] ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('local_international') }}</span>
            <span class="po-form-line">{{ $prContext['geographic_scope'] ?? '' }}</span>
        </div>
    </div>
    <div class="po-grid-col po-order-right">
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('category') }}</span>
            <span class="po-form-line">{{ $prContext['category'] ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('scope_type') }}</span>
            <span class="po-form-line">{{ $prContext['scope_type'] ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">{{ $printLabels->t('project') }}</span>
            <span class="po-form-line">{{ $prContext['project'] ?? '' }}</span>
        </div>
        @php
            $printPackage = trim((string) ($purchaseOrder->package ?? ''));
            if ($printPackage === '') {
                $printPackage = trim((string) ($prContext['package'] ?? ''));
            }
        @endphp
        @if (filled($printPackage))
            <div class="po-form-group">
                <span class="po-form-label">{{ $printLabels->t('package') }}</span>
                <span class="po-form-line">{{ $printPackage }}</span>
            </div>
        @endif
    </div>
</div>
