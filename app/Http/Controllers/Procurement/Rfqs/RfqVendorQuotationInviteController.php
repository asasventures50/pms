<?php

namespace App\Http\Controllers\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteLocale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Rfqs\StoreRfqVendorQuotationInviteRequest;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\Rfqs\RfqVendorQuotationInviteService;
use Illuminate\Http\RedirectResponse;

class RfqVendorQuotationInviteController extends Controller
{
    public function __construct(
        private readonly RfqVendorQuotationInviteService $inviteService,
    ) {}

    public function store(StoreRfqVendorQuotationInviteRequest $request, Rfq $rfq): RedirectResponse
    {
        if ($rfq->items()->doesntExist()) {
            return back()->withErrors([
                'vendor_quotation_invite' => 'Add RFQ line items before generating a vendor quotation link.',
            ]);
        }

        $vendor = Vendor::query()->findOrFail((int) $request->validated('vendor_id'));
        $locale = RfqVendorQuotationInviteLocale::from($request->validated('ui_locale'));

        $invite = $this->inviteService->createInvite(
            $rfq,
            $vendor,
            $locale,
            (bool) $request->validated('include_terms'),
            $request->user(),
        );

        return redirect()
            ->route('rfqs.show', $rfq)
            ->with('success', 'Vendor quotation link generated. Copy and send it via WhatsApp.')
            ->with('generated_vendor_quotation_invite_url', $invite->publicUrl())
            ->with('generated_vendor_quotation_invite_id', $invite->id);
    }
}
