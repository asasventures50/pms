@php
    $printLabels = $printLabels ?? \App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels::resolve(null);
    $companyName = strtoupper($buyer['name'] ?? $buyerCompany['name'] ?? 'ASAS VENTURES');
@endphp

<footer class="po-footer po-footer--screen-only" aria-hidden="true">
    <div class="po-footer-cell po-footer-left">{{ $printLabels->t('form_pr') }}</div>
    <div class="po-footer-cell po-footer-center">{{ $companyName }}</div>
    <div class="po-footer-cell po-footer-right"></div>
</footer>
