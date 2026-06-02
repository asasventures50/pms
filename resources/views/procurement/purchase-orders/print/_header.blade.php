<table class="po-header-table">
    <tr>
        <td class="po-header-logo">
            <img src="{{ $poLogoUrl }}" alt="ASAS VENTURES" class="po-logo-img"
                 @unless ($poLogoExists)
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='block';"
                 @endunless>
            <div class="po-logo-fallback" @if ($poLogoExists) style="display:none;" @endif>
                ASAS<br>VENTURES
            </div>
        </td>
        <td class="po-header-title">Purchase Order</td>
        <td class="po-header-dept">Procurement<br>Department</td>
    </tr>
</table>

<div class="po-company-info">
    <div class="po-company-name">{{ strtoupper($buyer['name'] ?? 'ASAS VENTURES') }}</div>
    @if ($buyer['address'] ?? null)
        <div>Address: {{ $buyer['address'] }}</div>
    @endif
    @if ($buyer['phone'] ?? null)
        <div>Phone: {{ $buyer['phone'] }}</div>
    @endif
    @if ($buyer['email'] ?? null)
        <div>Email: {{ $buyer['email'] }}</div>
    @endif
    @if ($buyer['fax'] ?? null)
        <div>FAX: {{ $buyer['fax'] }}</div>
    @endif
</div>

<div class="po-section-title">Order information</div>
<div class="po-grid-2">
    <div class="po-grid-col po-order-left">
        <div class="po-form-group">
            <span class="po-form-label">P.O. number:</span>
            <span class="po-form-line">{{ $purchaseOrder->po_number }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Date:</span>
            <span class="po-form-line">{{ $purchaseOrder->ordered_at?->format('d-m-Y') ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">P.R. number:</span>
            <span class="po-form-line">{{ $purchaseOrder->procurementRequest?->request_number ?? '' }}</span>
        </div>
    </div>
    <div class="po-grid-col po-order-right">
        <div class="po-form-group">
            <span class="po-form-label">Category:</span>
            <span class="po-form-line">{{ $prContext['category'] ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Scope Type:</span>
            <span class="po-form-line">{{ $prContext['scope_type'] ?? '' }}</span>
        </div>
        <div class="po-form-group">
            <span class="po-form-label">Project:</span>
            <span class="po-form-line">{{ $prContext['project'] ?? '' }}</span>
        </div>
    </div>
</div>
