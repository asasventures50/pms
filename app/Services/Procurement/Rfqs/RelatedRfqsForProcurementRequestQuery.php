<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Rfqs\Rfq;
use Illuminate\Database\Eloquent\Collection;

final class RelatedRfqsForProcurementRequestQuery
{
    /**
     * @return Collection<int, Rfq>
     */
    public function forProcurementRequest(ProcurementRequest $procurementRequest): Collection
    {
        $itemIds = $procurementRequest->items()->pluck('id');

        if ($itemIds->isEmpty()) {
            return new Collection;
        }

        return Rfq::query()
            ->whereHas('items', fn ($query) => $query->whereIn('procurement_request_item_id', $itemIds))
            ->with([
                'selectedVendorQuotation:id,quotation_number,vendor_company_name',
                'selectedBy:id,name',
            ])
            ->withCount('vendorQuotations')
            ->latest()
            ->get();
    }
}
