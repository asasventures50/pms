@php
    $companyName = strtoupper($buyer['name'] ?? $buyerCompany['name'] ?? \App\Enums\Procurement\BuyerCompany::NAME ?? 'ASAS VENTURES');
@endphp

{{-- Screen preview only; print uses @page margin boxes (see _page-setup) to avoid overlapping content --}}
<footer class="po-footer po-footer--screen-only" aria-hidden="true">
    <div class="po-footer-cell po-footer-left">Form PO</div>
    <div class="po-footer-cell po-footer-center">{{ $companyName }}</div>
    <div class="po-footer-cell po-footer-right"></div>
</footer>
