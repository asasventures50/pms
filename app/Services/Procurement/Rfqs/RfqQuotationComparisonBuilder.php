<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use Illuminate\Database\Eloquent\Collection;

final class RfqQuotationComparisonBuilder
{
    /**
     * @return array{
     *     columns: \Illuminate\Support\Collection<int, array{
     *         quotation: VendorQuotation,
     *         lines_by_rfq_item_id: \Illuminate\Support\Collection<int, \App\Models\Procurement\VendorQuotations\VendorQuotationItem>,
     *         is_lowest: bool,
     *         is_selected: bool
     *     }>,
     *     rfq_items: \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\Rfqs\RfqItem>,
     *     lowest_grand_total: float|null
     * }
     */
    public function build(Rfq $rfq): array
    {
        $rfq->loadMissing([
            'items.procurementRequestItem.procurementRequest',
            'vendorQuotations.items',
            'vendorQuotations.vendor',
            'selectedVendorQuotation',
            'selectedBy',
        ]);

        $quotations = $rfq->vendorQuotations;
        $lowestGrandTotal = $quotations->count() >= 2
            ? (float) $quotations->min(fn (VendorQuotation $quotation) => (float) ($quotation->grand_total ?? 0))
            : null;

        $columns = $quotations->map(function (VendorQuotation $quotation) use ($rfq, $lowestGrandTotal) {
            $grandTotal = (float) ($quotation->grand_total ?? 0);

            return [
                'quotation' => $quotation,
                'lines_by_rfq_item_id' => $quotation->items->keyBy('rfq_item_id'),
                'is_lowest' => $lowestGrandTotal !== null && abs($grandTotal - $lowestGrandTotal) < 0.001,
                'is_selected' => (int) $rfq->selected_vendor_quotation_id === (int) $quotation->id,
            ];
        });

        return [
            'columns' => $columns,
            'rfq_items' => $rfq->items,
            'lowest_grand_total' => $lowestGrandTotal,
        ];
    }
}
