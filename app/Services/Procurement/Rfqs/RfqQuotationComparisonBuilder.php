<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\VendorQuotations\VendorQuotationItem;
use Illuminate\Support\Collection as SupportCollection;

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
     *     lowest_grand_total: float|null,
     *     quotation_rows: array{payment_method: bool, notes: bool},
     *     line_rows: \Illuminate\Support\Collection<int, array{
     *         compliance: bool,
     *         brand_origin: bool,
     *         tax_rate: bool,
     *         tax_amount: bool,
     *         remarks: bool,
     *         lead_time: bool,
     *         warranty: bool
     *     }>
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

        $lineRows = $rfq->items->mapWithKeys(
            fn (RfqItem $line) => [$line->id => $this->lineRowVisibility($columns, $line)]
        );

        return [
            'columns' => $columns,
            'rfq_items' => $rfq->items,
            'lowest_grand_total' => $lowestGrandTotal,
            'quotation_rows' => [
                'payment_method' => $columns->contains(
                    fn (array $column) => filled($column['quotation']->payment_method)
                ),
                'notes' => $columns->contains(
                    fn (array $column) => filled($column['quotation']->notes)
                ),
            ],
            'line_rows' => $lineRows,
        ];
    }

    /**
     * @param  SupportCollection<int, array{
     *     quotation: VendorQuotation,
     *     lines_by_rfq_item_id: SupportCollection<int, VendorQuotationItem>,
     *     is_lowest: bool,
     *     is_selected: bool
     * }>  $columns
     * @return array{
     *     compliance: bool,
     *     brand_origin: bool,
     *     tax_rate: bool,
     *     tax_amount: bool,
     *     remarks: bool,
     *     lead_time: bool,
     *     warranty: bool
     * }
     */
    private function lineRowVisibility(SupportCollection $columns, RfqItem $line): array
    {
        return [
            'compliance' => $this->lineRowHasValue(
                $columns,
                $line,
                fn (?VendorQuotationItem $quoteLine) => $quoteLine?->compliance !== null
            ),
            'brand_origin' => $this->lineRowHasValue(
                $columns,
                $line,
                fn (?VendorQuotationItem $quoteLine) => filled($quoteLine?->brand_origin)
            ),
            'tax_rate' => $this->lineRowHasValue(
                $columns,
                $line,
                fn (?VendorQuotationItem $quoteLine) => $quoteLine?->tax_rate !== null
                    && (float) $quoteLine->tax_rate != 0.0
            ),
            'tax_amount' => $this->lineRowHasValue(
                $columns,
                $line,
                fn (?VendorQuotationItem $quoteLine) => $quoteLine !== null
                    && (float) $quoteLine->tax != 0.0
            ),
            'remarks' => $this->lineRowHasValue(
                $columns,
                $line,
                fn (?VendorQuotationItem $quoteLine) => filled($quoteLine?->remarks)
            ),
            'lead_time' => $this->lineRowHasValue(
                $columns,
                $line,
                fn (?VendorQuotationItem $quoteLine) => filled($quoteLine?->lead_time)
            ),
            'warranty' => $this->lineRowHasValue(
                $columns,
                $line,
                fn (?VendorQuotationItem $quoteLine) => filled($quoteLine?->warranty)
            ),
        ];
    }

    /**
     * @param  SupportCollection<int, array{
     *     quotation: VendorQuotation,
     *     lines_by_rfq_item_id: SupportCollection<int, VendorQuotationItem>,
     *     is_lowest: bool,
     *     is_selected: bool
     * }>  $columns
     */
    private function lineRowHasValue(
        SupportCollection $columns,
        RfqItem $line,
        callable $hasValue
    ): bool {
        foreach ($columns as $column) {
            $quoteLine = $column['lines_by_rfq_item_id']->get($line->id);

            if ($hasValue($quoteLine)) {
                return true;
            }
        }

        return false;
    }
}
