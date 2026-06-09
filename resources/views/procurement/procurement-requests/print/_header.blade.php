@php
    $printLabels = $printLabels ?? \App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels::resolve(null);
    $prCompany = $prCompany ?? null;
    $logoFallback = $prCompany?->logoFallbackHtml() ?? 'ASAS<br>VENTURES';
    $logoAlt = $prCompany?->label() ?? ($buyer['name'] ?? 'Company');
    $departmentLines = explode(' ', $printLabels->t('department'), 2);
@endphp
<table class="po-header-table">
    <tr>
        <td class="po-header-logo">
            <img src="{{ $poLogoUrl }}" alt="{{ $logoAlt }}" class="po-logo-img"
                 @unless ($poLogoExists)
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='block';"
                 @endunless>
            <div class="po-logo-fallback" @if ($poLogoExists) style="display:none;" @endif>
                {!! $logoFallback !!}
            </div>
        </td>
        <td class="po-header-title">{{ $printLabels->t('document_title') }}</td>
        <td class="po-header-dept">{{ $departmentLines[0] }}@if (isset($departmentLines[1]))<br>{{ $departmentLines[1] }}@endif</td>
    </tr>
</table>
