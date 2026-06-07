@php
    $companyName = strtoupper($buyer['name'] ?? $buyerCompany['name'] ?? \App\Enums\Procurement\BuyerCompany::NAME ?? 'ASAS VENTURES');
@endphp

<footer class="po-footer po-footer--screen-only" aria-hidden="true">
    <div class="po-footer-cell po-footer-left">Form PR</div>
    <div class="po-footer-cell po-footer-center">{{ $companyName }}</div>
    <div class="po-footer-cell po-footer-right"></div>
</footer>
