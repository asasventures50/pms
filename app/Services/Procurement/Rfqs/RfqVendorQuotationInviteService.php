<?php

namespace App\Services\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteLocale;
use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteStatus;
use App\Enums\Procurement\VendorQuotations\VendorQuotationDocumentType;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use App\Services\Procurement\PurchaseOrders\VendorPurchaseOrderSnapshot;
use App\Services\Procurement\VendorQuotations\VendorQuotationCodeGenerator;
use App\Services\Procurement\VendorQuotations\VendorQuotationPersistenceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RfqVendorQuotationInviteService
{
    public function __construct(
        private readonly VendorQuotationPersistenceService $quotationPersistence,
        private readonly VendorQuotationCodeGenerator $codeGenerator,
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    public function createInvite(
        Rfq $rfq,
        Vendor $vendor,
        RfqVendorQuotationInviteLocale $uiLocale,
        bool $includeTerms,
        User $createdBy,
    ): RfqVendorQuotationInvite {
        return DB::transaction(function () use ($rfq, $vendor, $uiLocale, $includeTerms, $createdBy) {
            RfqVendorQuotationInvite::query()
                ->where('rfq_id', $rfq->id)
                ->where('vendor_id', $vendor->id)
                ->where('status', RfqVendorQuotationInviteStatus::Pending)
                ->update(['status' => RfqVendorQuotationInviteStatus::Revoked]);

            return RfqVendorQuotationInvite::query()->create([
                'rfq_id' => $rfq->id,
                'vendor_id' => $vendor->id,
                'token' => $this->generateToken(),
                'ui_locale' => $uiLocale,
                'include_terms' => $includeTerms,
                'status' => RfqVendorQuotationInviteStatus::Pending,
                'created_by' => $createdBy->id,
            ]);
        });
    }

    /**
     * @param  array{
     *     vendor_rep_name?: string|null,
     *     vendor_rep_email?: string|null,
     *     vendor_rep_phone?: string|null,
     *     notes?: string|null,
     *     items: list<array<string, mixed>>,
     *     attachment?: UploadedFile|null
     * }  $payload
     */
    public function submitQuotation(RfqVendorQuotationInvite $invite, array $payload): VendorQuotation
    {
        if (! $invite->isPending()) {
            throw new \RuntimeException('This quotation link is no longer accepting submissions.');
        }

        $invite->loadMissing(['rfq.items', 'vendor']);

        $rfq = $invite->rfq;
        $vendor = $invite->vendor;

        if (! $rfq instanceof Rfq || ! $vendor instanceof Vendor) {
            throw new \RuntimeException('Invitation is missing RFQ or vendor.');
        }

        if ($rfq->items->isEmpty()) {
            throw new \RuntimeException('This RFQ has no line items.');
        }

        $items = VendorQuotationPersistenceService::normalizeItems(
            $rfq,
            $payload['items'] ?? [],
        );

        if ($items === []) {
            throw new \InvalidArgumentException('Add a unit price for at least one item.');
        }

        $hasPrice = false;
        foreach ($items as $row) {
            if ((float) ($row['unit_price'] ?? 0) > 0) {
                $hasPrice = true;
                break;
            }
        }

        if (! $hasPrice) {
            throw new \InvalidArgumentException('Enter a unit price for at least one item.');
        }

        $snapshot = VendorPurchaseOrderSnapshot::fromVendor($vendor);
        $attachment = $payload['attachment'] ?? null;

        return DB::transaction(function () use ($invite, $rfq, $vendor, $snapshot, $items, $payload, $attachment) {
            $locked = RfqVendorQuotationInvite::query()
                ->whereKey($invite->id)
                ->lockForUpdate()
                ->first();

            if (! $locked instanceof RfqVendorQuotationInvite || ! $locked->isPending()) {
                throw new \RuntimeException('This quotation link is no longer accepting submissions.');
            }

            $header = [
                'quotation_number' => $this->codeGenerator->nextForRfq($rfq),
                'created_by' => $locked->created_by,
                'vendor_id' => $vendor->id,
                'vendor_company_name' => $snapshot['vendor_company_name'] ?? $vendor->name,
                'vendor_contact' => $vendor->primary_contact_name ?: null,
                'vendor_email' => $snapshot['vendor_email'] ?? null,
                'vendor_phone' => $snapshot['vendor_phone'] ?? null,
                'vendor_address' => null,
                'notes' => isset($payload['notes']) ? trim((string) $payload['notes']) : null,
                'vendor_rep_name' => isset($payload['vendor_rep_name']) ? trim((string) $payload['vendor_rep_name']) : null,
                'vendor_rep_email' => isset($payload['vendor_rep_email']) ? trim((string) $payload['vendor_rep_email']) : null,
                'vendor_rep_phone' => isset($payload['vendor_rep_phone']) ? trim((string) $payload['vendor_rep_phone']) : null,
                'vendor_declarations' => [],
            ];

            if ($header['notes'] === '') {
                $header['notes'] = null;
            }
            if ($header['vendor_rep_name'] === '') {
                $header['vendor_rep_name'] = null;
            }
            if ($header['vendor_rep_email'] === '') {
                $header['vendor_rep_email'] = null;
            }
            if ($header['vendor_rep_phone'] === '') {
                $header['vendor_rep_phone'] = null;
            }

            $documentUploads = [];
            if ($attachment instanceof UploadedFile && $attachment->isValid()) {
                $documentUploads[VendorQuotationDocumentType::OtherSupportingDocuments->value] = $attachment;
            }

            $quotation = $this->quotationPersistence->create(
                $rfq,
                $header,
                $items,
                $documentUploads,
            );

            $locked->status = RfqVendorQuotationInviteStatus::Submitted;
            $locked->vendor_quotation_id = $quotation->id;
            $locked->submitted_at = now();
            $locked->save();

            return $quotation;
        });
    }

    /**
     * Live general terms (global + PR scope types) plus RFQ custom/special terms.
     *
     * @return list<string>
     */
    public function resolveTermsForInvite(RfqVendorQuotationInvite $invite, string $locale): array
    {
        if (! $invite->include_terms) {
            return [];
        }

        $invite->loadMissing(['rfq.items.procurementRequestItem']);

        $rfq = $invite->rfq;
        if (! $rfq instanceof Rfq) {
            return [];
        }

        $scopeTypes = $this->termsService->scopeTypesFromNormalizedItems(
            $rfq->items->map(fn ($item) => [
                'procurement_request_item_id' => $item->procurement_request_item_id,
            ])->all()
        );

        $general = $this->termsService->activeTextsForScopeTypes($scopeTypes, $locale);
        $custom = $this->termsService->resolveStoredCustomTermsForLocale($rfq->terms, $locale);

        return array_values(array_merge($general, $custom));
    }

    private function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (RfqVendorQuotationInvite::query()->where('token', $token)->exists());

        return $token;
    }
}
