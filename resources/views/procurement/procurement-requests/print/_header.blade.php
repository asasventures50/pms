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
        <td class="po-header-title">Procurement Request</td>
        <td class="po-header-dept">Procurement<br>Department</td>
    </tr>
</table>
