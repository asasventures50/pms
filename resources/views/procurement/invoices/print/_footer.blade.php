<div class="inv-footer">
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
