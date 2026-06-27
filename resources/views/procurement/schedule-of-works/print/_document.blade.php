<div class="inv-print-document">
    <div class="inv-print-main">
        @include('procurement.schedule-of-works.print._header')
        @include('procurement.schedule-of-works.print._recipient')
        @include('procurement.schedule-of-works.print._items')
        @include('procurement.schedule-of-works.print._notes')
        @include('procurement.schedule-of-works.print._bank')
        @include('procurement.schedule-of-works.print._terms', ['terms' => $terms ?? []])
    </div>
    <div class="inv-print-bottom">
        @include('procurement.schedule-of-works.print._pre_footer')
        @include('procurement.schedule-of-works.print._footer')
    </div>
</div>
