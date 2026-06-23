<div class="inv-footer">
    @if ($buyer['commercial_registry'] ?? null)
        <div class="inv-footer-registry">{{ $buyer['commercial_registry'] }}</div>
    @endif
    @if ($buyer['company_legal_type'] ?? null)
        <div class="inv-footer-legal-type">{{ $buyer['company_legal_type'] }}</div>
    @endif
    <div class="inv-footer-contact">
        @if ($buyer['phone'] ?? null)
            <div>هاتف: <span class="inv-ltr">{{ $buyer['phone'] }}</span></div>
        @endif
        @if ($buyer['email'] ?? null)
            <div>بريد إلكتروني: <span class="inv-ltr">{{ $buyer['email'] }}</span></div>
        @endif
        @if ($buyer['fax'] ?? null)
            <div>فاكس: <span class="inv-ltr">{{ $buyer['fax'] }}</span></div>
        @endif
    </div>
</div>
