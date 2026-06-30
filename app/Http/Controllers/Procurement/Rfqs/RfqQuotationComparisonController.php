<?php

namespace App\Http\Controllers\Procurement\Rfqs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Rfqs\SelectRfqQuotationRequest;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Services\Procurement\Rfqs\RfqQuotationComparisonBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqQuotationComparisonController extends Controller
{
    public function show(Request $request, Rfq $rfq, RfqQuotationComparisonBuilder $builder): View
    {
        abort_unless($request->user()?->canViewQuotationComparison($rfq), 403);

        $rfq->load([
            'selectedVendorQuotation',
            'selectedBy',
            'items.procurementRequestItem.procurementRequest',
        ]);

        return view('procurement.rfqs.comparison', [
            'rfq' => $rfq,
            'comparison' => $builder->build($rfq),
            'canSelect' => $request->user()?->canSelectQuotationForRfq($rfq) ?? false,
        ]);
    }

    public function select(SelectRfqQuotationRequest $request, Rfq $rfq): RedirectResponse
    {
        $quotationId = (int) $request->validated('vendor_quotation_id');

        /** @var VendorQuotation $quotation */
        $quotation = $rfq->vendorQuotations()->whereKey($quotationId)->firstOrFail();

        $rfq->update([
            'selected_vendor_quotation_id' => $quotation->id,
            'selected_by' => $request->user()->id,
            'selected_at' => now(),
        ]);

        return redirect()
            ->route('rfqs.comparison.show', $rfq)
            ->with('success', 'Selected '.$quotation->quotation_number.' as the preferred quotation.');
    }

    public function clearSelection(Request $request, Rfq $rfq): RedirectResponse
    {
        abort_unless($request->user()?->canSelectQuotationForRfq($rfq), 403);

        $rfq->update([
            'selected_vendor_quotation_id' => null,
            'selected_by' => null,
            'selected_at' => null,
        ]);

        return redirect()
            ->route('rfqs.comparison.show', $rfq)
            ->with('success', 'Cleared the selected quotation for this RFQ.');
    }
}
