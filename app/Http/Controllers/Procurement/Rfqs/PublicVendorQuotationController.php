<?php

namespace App\Http\Controllers\Procurement\Rfqs;

use App\Enums\Procurement\BuyerCompany;
use App\Http\Controllers\Controller;
use App\Http\Middleware\SetPublicFormLocale;
use App\Http\Requests\Procurement\Rfqs\StorePublicVendorQuotationRequest;
use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Services\Procurement\Rfqs\RfqVendorQuotationInviteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicVendorQuotationController extends Controller
{
    public function __construct(
        private readonly RfqVendorQuotationInviteService $inviteService,
    ) {}

    public function show(Request $request, RfqVendorQuotationInvite $invite): View
    {
        $this->applyLocale($request, $invite);

        $invite->load([
            'rfq.items',
            'rfq.creator',
            'vendor',
            'vendorQuotation.items.rfqItem',
        ]);

        $locale = app()->getLocale();
        $terms = $this->inviteService->resolveTermsForInvite($invite, $locale);

        return view('procurement.vendor-quotation-invites.public-form', [
            'invite' => $invite,
            'rfq' => $invite->rfq,
            'vendor' => $invite->vendor,
            'terms' => $terms,
            'buyerCompany' => BuyerCompany::forDisplay($invite->rfq),
            'readOnly' => $invite->isReadOnly(),
            'quotation' => $invite->vendorQuotation,
            'hideLocaleToggle' => $invite->ui_locale->locksLocale(),
            'allowLocaleChoice' => ! $invite->ui_locale->locksLocale(),
        ]);
    }

    public function store(StorePublicVendorQuotationRequest $request, RfqVendorQuotationInvite $invite): RedirectResponse
    {
        $this->applyLocale($request, $invite);

        if (! $invite->isPending()) {
            return redirect()
                ->route('vendor-quotation-invite.show', $invite)
                ->with('info', __('vendor_quotation_invite.already_submitted'));
        }

        $validated = $request->validated();

        try {
            $this->inviteService->submitQuotation($invite, [
                'vendor_rep_name' => $validated['vendor_rep_name'] ?? null,
                'vendor_rep_email' => $validated['vendor_rep_email'] ?? null,
                'vendor_rep_phone' => $validated['vendor_rep_phone'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'items' => $validated['items'] ?? [],
                'attachment' => $request->file('attachment'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('vendor-quotation-invite.show', $invite)
                ->with('info', $e->getMessage());
        }

        return redirect()
            ->route('vendor-quotation-invite.show', $invite)
            ->with('success', __('vendor_quotation_invite.submitted_success'));
    }

    private function applyLocale(Request $request, RfqVendorQuotationInvite $invite): void
    {
        $locked = $invite->ui_locale->lockedLocale();

        if ($locked !== null) {
            app()->setLocale($locked);

            return;
        }

        if ($request->has('lang')) {
            $lang = (string) $request->query('lang');
            if (in_array($lang, ['en', 'ar'], true)) {
                $request->session()->put(SetPublicFormLocale::SESSION_KEY, $lang);
            }
        }

        $locale = (string) $request->session()->get(SetPublicFormLocale::SESSION_KEY, 'en');
        if (! in_array($locale, ['en', 'ar'], true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);
    }
}
