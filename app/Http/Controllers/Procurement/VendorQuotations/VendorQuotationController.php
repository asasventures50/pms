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
use App\Services\Procurement\Vendors\VendorSelectOptions;
use App\Services\Procurement\VendorQuotations\VendorQuotationCodeGenerator;
use App\Services\Procurement\VendorQuotations\VendorQuotationPersistenceService;
use App\Services\Procurement\VendorQuotations\VendorQuotationRfqContext;
use App\Services\Procurement\VendorQuotations\VendorQuotationSignatureStorage;
use App\Support\Procurement\VendorQuotations\VendorQuotationDeclarations;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorQuotationController extends Controller
{
    public function __construct(
        private readonly VendorQuotationPersistenceService $persistence,
        private readonly VendorQuotationSignatureStorage $signatureStorage,
    ) {}

    public function create(Rfq $rfq): View
    {
        $rfq->load([
            'items.procurementRequestItem.procurementRequest',
            'vendorQuotations.vendor',
        ]);

        if ($rfq->items->isEmpty()) {
            abort(422, 'This RFQ has no line items. Add request lines before creating a vendor quotation.');
        }

        return view('procurement.vendor-quotations.create', [
            'rfq' => $rfq,
            'quotation' => null,
            'nextCode' => app(VendorQuotationCodeGenerator::class)->nextForRfq($rfq),
            'selectedVendor' => null,
            'vendorSelectOptions' => VendorSelectOptions::all(),
            'lineItems' => $this->defaultLineItemsFromRfq($rfq),
            'complianceOptions' => QuotationCompliance::cases(),
            'documentTypes' => VendorQuotationDocumentType::cases(),
            'rfqContext' => VendorQuotationRfqContext::resolve($rfq),
            'buyerCompany' => BuyerCompany::forDisplay($rfq),
            'declarations' => VendorQuotationDeclarations::all(),
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

        unset($validated['items'], $validated['remove_documents'], $validated['vendor_rep_signature_file'], $validated['remove_signature']);

        $validated['quotation_number'] ??= app(VendorQuotationCodeGenerator::class)->nextForRfq($rfq);
        $validated['created_by'] = $request->user()->id;

        $quotation = $this->persistence->create(
            $rfq,
            $validated,
            $items,
            $this->documentUploadsFromRequest($request),
            $request->input('remove_documents', []),
        );

        $this->syncSignatureFromRequest($request, $quotation);

        return redirect()
            ->route('rfqs.quotations.show', [$rfq, $quotation])
            ->with('success', 'Vendor quotation saved. You can add another offer to compare prices.');
    }

    public function show(Rfq $rfq, VendorQuotation $quotation): View
    {
        $this->ensureQuotationBelongsToRfq($rfq, $quotation);

        return view('procurement.vendor-quotations.show', $this->quotationDocumentViewData($rfq, $quotation));
    }

    public function print(Rfq $rfq, VendorQuotation $quotation): View
    {
        $this->ensureQuotationBelongsToRfq($rfq, $quotation);

        return view('procurement.vendor-quotations.print', $this->quotationDocumentViewData($rfq, $quotation));
    }

    public function edit(Rfq $rfq, VendorQuotation $quotation): View
    {
        $this->ensureQuotationBelongsToRfq($rfq, $quotation);

        $rfq->load(['items.procurementRequestItem.procurementRequest']);
        $quotation->load(['items.rfqItem.procurementRequestItem', 'vendor']);

        return view('procurement.vendor-quotations.edit', [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'selectedVendor' => $quotation->vendor,
            'vendorSelectOptions' => VendorSelectOptions::all(),
            'lineItems' => $this->lineItemsForForm($rfq, $quotation),
            'complianceOptions' => QuotationCompliance::cases(),
            'documentTypes' => VendorQuotationDocumentType::cases(),
            'rfqContext' => VendorQuotationRfqContext::resolve($rfq),
            'buyerCompany' => BuyerCompany::forDisplay($rfq),
            'declarations' => VendorQuotationDeclarations::all(),
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

        unset($validated['items'], $validated['remove_documents'], $validated['vendor_rep_signature_file'], $validated['remove_signature']);

        $this->persistence->update(
            $quotation,
            $validated,
            $items,
            $this->documentUploadsFromRequest($request),
            $request->input('remove_documents', []),
        );

        $this->syncSignatureFromRequest($request, $quotation->fresh());

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
            'request_lead_time' => $row->request_lead_time,
            'quantity_quoted' => $row->quantity,
            'compliance' => '',
            'alternative_if_no' => '',
            'item_description_if_no' => '',
            'brand' => '',
            'model' => '',
            'country_of_origin' => '',
            'brand_origin' => '',
            'unit_price' => '',
            'currency' => '',
            'total_price' => '',
            'discount' => '',
            'tax_rate' => '',
            'tax' => '',
            'delivery_charges' => '',
            'installation' => '',
            'lead_time' => '',
            'warranty' => '',
            'remarks' => '',
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
                'request_lead_time' => $rfqItem->request_lead_time,
                'quantity_quoted' => $quoted?->quantity_quoted ?? $rfqItem->quantity,
                'compliance' => $quoted?->compliance?->value ?? '',
                'alternative_if_no' => $quoted?->alternative_if_no ?? '',
                'item_description_if_no' => $quoted?->item_description_if_no ?? '',
                'brand' => $quoted?->brand ?? '',
                'model' => $quoted?->model ?? '',
                'country_of_origin' => $quoted?->country_of_origin ?? '',
                'brand_origin' => $quoted?->brand_origin ?? '',
                'unit_price' => $quoted?->unit_price ?? '',
                'currency' => $quoted?->currency ?? '',
                'total_price' => $quoted?->total_price ?? '',
                'discount' => $quoted?->discount ?? '',
                'tax_rate' => $quoted?->tax_rate ?? '',
                'tax' => $quoted?->tax ?? '',
                'delivery_charges' => $quoted?->delivery_charges ?? '',
                'installation' => $quoted?->installation ?? '',
                'lead_time' => $quoted?->lead_time ?? '',
                'warranty' => $quoted?->warranty ?? '',
                'remarks' => $quoted?->remarks ?? '',
            ];
        })->all();
    }

    /**
     * @return array<string, \Illuminate\Http\UploadedFile|null>
     */
    private function documentUploadsFromRequest(StoreVendorQuotationRequest $request): array
    {
        $uploads = [];

        foreach (VendorQuotationDocumentType::cases() as $type) {
            $uploads[$type->value] = $request->file($type->inputName());
        }

        return $uploads;
    }

    /**
     * @return array<string, mixed>
     */
    private function quotationDocumentViewData(Rfq $rfq, VendorQuotation $quotation): array
    {
        $quotation->load([
            'vendor',
            'creator',
            'items.rfqItem.procurementRequestItem.procurementRequest',
        ]);

        $rfq->load('vendorQuotations.vendor');

        return [
            'rfq' => $rfq,
            'quotation' => $quotation,
            'buyerCompany' => BuyerCompany::forDisplay($rfq),
            'documentTypes' => VendorQuotationDocumentType::cases(),
            'rfqContext' => VendorQuotationRfqContext::resolve($rfq),
            'declarations' => VendorQuotationDeclarations::all(),
        ];
    }

    private function syncSignatureFromRequest(StoreVendorQuotationRequest $request, VendorQuotation $quotation): void
    {
        if ($request->boolean('remove_signature') && $quotation->vendor_rep_signature_path) {
            $this->signatureStorage->delete($quotation->vendor_rep_signature_path);
            $quotation->vendor_rep_signature_path = null;
            $quotation->save();
        }

        if ($request->hasFile('vendor_rep_signature_file')) {
            $path = $this->signatureStorage->store($quotation, $request->file('vendor_rep_signature_file'));
            $quotation->vendor_rep_signature_path = $path;
            $quotation->save();
        }
    }
}
