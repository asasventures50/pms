<div class="inv-footer">
    @php
        $legalParts = array_filter([
            $buyer['company_legal_type'] ?? null,
            $buyer['commercial_registry'] ?? null,
        ]);
        $contactParts = [];
        if ($buyer['phone'] ?? null) {
            $contactParts[] = ($printLabels->isRtl() ? 'هاتف: ' : 'Tel: ').'<span class="inv-ltr" dir="ltr">'.$buyer['phone'].'</span>';
        }
        if ($buyer['email'] ?? null) {
            $contactParts[] = ($printLabels->isRtl() ? 'بريد إلكتروني: ' : 'Email: ').'<span class="inv-ltr" dir="ltr">'.$buyer['email'].'</span>';
        }
        if ($buyer['fax'] ?? null) {
            $contactParts[] = ($printLabels->isRtl() ? 'فاكس: ' : 'Fax: ').'<span class="inv-ltr" dir="ltr">'.$buyer['fax'].'</span>';
        }
    @endphp
    @if (count($legalParts))
        <div class="inv-footer-legal">{!! implode(' <span class="inv-footer-sep">|</span> ', $legalParts) !!}</div>
    @endif
    @if (count($contactParts))
        <div class="inv-footer-contact">{!! implode(' <span class="inv-footer-sep">|</span> ', $contactParts) !!}</div>
    @endif
</div>
