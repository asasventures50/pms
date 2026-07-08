<div class="inv-print-document">
    <table class="inv-print-table">
        <tbody>
            <tr>
                <td class="inv-print-cell">
                    <div class="inv-print-main">
                        @include('procurement.invoices.print._header')
                        @include('procurement.invoices.print._recipient')
                        @include('procurement.invoices.print._items')
                        @include('procurement.invoices.print._notes')
                        @include('procurement.invoices.print._bank')
                    </div>
                    <div class="inv-print-bottom">
                        @include('procurement.invoices.print._pre_footer')
                    </div>
                </td>
            </tr>
        </tbody>
        <tfoot class="inv-print-tfoot">
            <tr>
                <td class="inv-print-cell">
                    <div class="inv-footer-space" aria-hidden="true"></div>
                </td>
            </tr>
        </tfoot>
    </table>

    @include('procurement.invoices.print._footer')
</div>
