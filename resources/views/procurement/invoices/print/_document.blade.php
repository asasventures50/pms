<div class="inv-print-document">
    <div class="inv-print-main">
        @include('procurement.invoices.print._header')
        @include('procurement.invoices.print._recipient')
        @include('procurement.invoices.print._items')
        @include('procurement.invoices.print._notes')
    </div>
    <div class="inv-print-bottom">
        @include('procurement.invoices.print._pre_footer')
        @include('procurement.invoices.print._footer')
    </div>
</div>
