<?php

namespace App\Http\Controllers\Procurement\VendorQuotations;

use App\Enums\Procurement\BuyerCompany;
use App\Enums\Procurement\VendorQuotations\QuotationCompliance;
use App\Enums\Procurement\VendorQuotations\VendorQuotationDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\VendorQuotations\StoreVendorQuotationRequest;
use App\Http\Requests\Procurement\VendorQuotations\UpdateVendorQuotationRequest;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\VendorQuotations\VendorQuotationCodeGenerator;
use App\Services\Procurement\VendorQuotations\VendorQuotationPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorQuotationController extends Controller
{
    public function __construct(
        private readonly VendorQuotationPersistenceService $persistence,
    ) {}

    public function create(Rfq $rfq): View
    {
        $rfq->load([
            'items.procurementRequestItem.procurementRequest',
        ]);

        if ($rfq->items->isEmpty()) {
            abort(422, 'This RFQ has no line items. Add request lines before creating a vendor quotation.');
        }

        return view('procurement.vendor-quotations.create', [
            'rfq' => $rfq,
            'quotation' => null,
            'nextCode' => app(VendorQuotationCodeGenerator::class)->nextForRfq($rfq),
            'vendors' => Vendor::query()->orderBy('name')->get(['id', 'vendor_code', 'name']),
            'lineItems' => $this->defaultLineItemsFromRfq($rfq),
            'complianceOptions' => QuotationCompliance::cases(),
            'documentTypes' => VendorQuotationDocumentType::cases(),
        ]);
    }

    public function store(StoreVendorQuotationRequest $request, Rfq $rfq): RedirectResponse
    {
        $validated = $request->validated();
        $items = VendorQuotationPersistenceService::normalizeItems(
            $rfq->load('items'),
            $validated['items'] ?? [],
        );

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Add pricing for at least one RFQ line item.']);
        }

        unset($validated['items'], $validated['remove_documents']);

        $validated['quotation_number'] ??= app(VendorQuotationCodeGenerator::class)->nextForRfq($rfq);
        $validated['created_by'] = $request->user()->id;

        $quotation = $this->persistence->create(
            $rfq,
            $validated,
            $items,
            $this->documentUploadsFromRequest($request),
            $request->input('remove_documents', []),
        );

        return redirect()
            ->route('rfqs.quotations.show', [$rfq, $quotation])
            ->with('success', 'Vendor quotation saved successfully.');
    }

    public function show(Rfq $rfq, VendorQuotation $quotation): View
    {
        $this->ensureQuotationBelongsToRfq($rfq, $quotation);

        $quotation->load([
            'vendor',
            'creator',
            'items.rfqItem.procurementRequestItem.procurementRequest',
        ]);

        return view('procurement.vendor-quotations.show', [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'buyerCompany' => BuyerCompany::forDisplay($rfq),
            'documentTypes' => VendorQuotationDocumentType::cases(),
        ]);
    }

    public function edit(Rfq $rfq, VendorQuotation $quotation): View
    {
        $this->ensureQuotationBelongsToRfq($rfq, $quotation);

        $rfq->load(['items.procurementRequestItem.procurementRequest']);
        $quotation->load(['items.rfqItem.procurementRequestItem']);

        return view('procurement.vendor-quotations.edit', [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'vendors' => Vendor::query()->orderBy('name')->get(['id', 'vendor_code', 'name']),
            'lineItems' => $this->lineItemsForForm($rfq, $quotation),
            'complianceOptions' => QuotationCompliance::cases(),
            'documentTypes' => VendorQuotationDocumentType::cases(),
        ]);
    }

    public function update(UpdateVendorQuotationRequest $request, Rfq $rfq, VendorQuotation $quotation): RedirectResponse
    {
        $this->ensureQuotationBelongsToRfq($rfq, $quotation);

        $validated = $request->validated();
        $items = VendorQuotationPersistenceService::normalizeItems(
            $rfq->load('items'),
            $validated['items'] ?? [],
        );

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Add pricing for at least one RFQ line item.']);
        }

        unset($validated['items'], $validated['remove_documents']);

        $this->persistence->update(
            $quotation,
            $validated,
            $items,
            $this->documentUploadsFromRequest($request),
            $request->input('remove_documents', []),
        );

        return redirect()
            ->route('rfqs.quotations.show', [$rfq, $quotation])
            ->with('success', 'Vendor quotation updated successfully.');
    }

    public function destroy(Rfq $rfq, VendorQuotation $quotation): RedirectResponse
    {
        $this->ensureQuotationBelongsToRfq($rfq, $quotation);

        $quotation->delete();

        return redirect()
            ->route('rfqs.show', $rfq)
            ->with('success', 'Vendor quotation deleted.');
    }

    private function ensureQuotationBelongsToRfq(Rfq $rfq, VendorQuotation $quotation): void
    {
        abort_unless($quotation->rfq_id === $rfq->id, 404);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function defaultLineItemsFromRfq(Rfq $rfq): array
    {
        return $rfq->items->map(fn ($row) => [
            'rfq_item_id' => $row->id,
            'item_number' => $row->item,
            'description' => $row->description,
            'quantity' => $row->quantity,
            'unit' => $row->unit,
            'delivery_location' => $row->procurementRequestItem?->delivery_location,
            'compliance' => '',
            'alternative_if_no' => '',
            'item_description_if_no' => '',
            'brand_origin' => '',
            'unit_price' => '',
            'currency' => '',
            'total_price' => '',
            'tax' => '',
            'lead_time' => '',
            'warranty' => '',
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function lineItemsForForm(Rfq $rfq, VendorQuotation $quotation): array
    {
        $quotedByRfqItem = $quotation->items->keyBy('rfq_item_id');

        return $rfq->items->map(function ($rfqItem) use ($quotedByRfqItem) {
            $quoted = $quotedByRfqItem->get($rfqItem->id);

            return [
                'rfq_item_id' => $rfqItem->id,
                'item_number' => $rfqItem->item,
                'description' => $rfqItem->description,
                'quantity' => $rfqItem->quantity,
                'unit' => $rfqItem->unit,
                'delivery_location' => $rfqItem->procurementRequestItem?->delivery_location,
                'compliance' => $quoted?->compliance?->value ?? '',
                'alternative_if_no' => $quoted?->alternative_if_no ?? '',
                'item_description_if_no' => $quoted?->item_description_if_no ?? '',
                'brand_origin' => $quoted?->brand_origin ?? '',
                'unit_price' => $quoted?->unit_price ?? '',
                'currency' => $quoted?->currency ?? '',
                'total_price' => $quoted?->total_price ?? '',
                'tax' => $quoted?->tax ?? '',
                'lead_time' => $quoted?->lead_time ?? '',
                'warranty' => $quoted?->warranty ?? '',
            ];
        })->all();
    }

    /**
     * @return array<string, \Illuminate\Http\UploadedFile|null>
     */
    private function documentUploadsFromRequest(StoreVendorQuotationRequest $request): array
    {
        return [
            VendorQuotationDocumentType::CommercialRegistration->value => $request->file('document_commercial_registration'),
            VendorQuotationDocumentType::CompanyProfile->value => $request->file('document_company_profile'),
            VendorQuotationDocumentType::TechnicalDatasheet->value => $request->file('document_technical_datasheet'),
        ];
    }
}
