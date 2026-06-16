<?php

namespace App\Services\Procurement\VendorQuotations;

use App\Enums\Procurement\VendorQuotations\QuotationCompliance;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\VendorQuotations\VendorQuotationItem;
use App\Support\Procurement\VendorQuotations\VendorQuotationDeclarations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class VendorQuotationPersistenceService
{
    public function __construct(
        private readonly VendorQuotationDocumentStorage $documentStorage,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, UploadedFile|null>  $documentUploads
     * @param  list<string>  $removeDocuments
     */
    public function create(Rfq $rfq, array $header, array $items, array $documentUploads = [], array $removeDocuments = []): VendorQuotation
    {
        return DB::transaction(function () use ($rfq, $header, $items, $documentUploads, $removeDocuments) {
            $header = $this->applyGrandTotal($header, $items);
            $header['rfq_id'] = $rfq->id;
            $header['vendor_declarations'] = VendorQuotationDeclarations::normalize($header['vendor_declarations'] ?? null);

            $quotation = VendorQuotation::query()->create($header);
            $this->syncItems($quotation, $items, $rfq);

            $documents = $this->documentStorage->removeKeys($quotation, $removeDocuments);
            $documents = $this->documentStorage->mergeUploads($quotation, $documentUploads, $documents);
            $quotation->documents_attached = $documents !== [] ? $documents : null;
            $quotation->save();

            return $quotation->load(['items.rfqItem.procurementRequestItem', 'vendor', 'creator', 'rfq']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @param  array<string, UploadedFile|null>  $documentUploads
     * @param  list<string>  $removeDocuments
     */
    public function update(
        VendorQuotation $quotation,
        array $header,
        array $items,
        array $documentUploads = [],
        array $removeDocuments = [],
    ): VendorQuotation {
        return DB::transaction(function () use ($quotation, $header, $items, $documentUploads, $removeDocuments) {
            $header = $this->applyGrandTotal($header, $items);
            $header['vendor_declarations'] = VendorQuotationDeclarations::normalize($header['vendor_declarations'] ?? null);
            $quotation->fill($header);
            $quotation->save();

            $quotation->load('rfq');
            $this->syncItems($quotation, $items, $quotation->rfq);
            $quotation->refresh();

            $documents = $this->documentStorage->removeKeys($quotation, $removeDocuments);
            $documents = $this->documentStorage->mergeUploads($quotation, $documentUploads, $documents);
            $quotation->documents_attached = $documents !== [] ? $documents : null;
            $quotation->save();

            return $quotation->load(['items.rfqItem.procurementRequestItem', 'vendor', 'creator', 'rfq']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $rawItems
     * @return list<array<string, mixed>>
     */
    public static function normalizeItems(Rfq $rfq, array $rawItems): array
    {
        $rfqItems = $rfq->items()->get()->keyBy('id');
        $normalized = [];

        foreach ($rawItems as $row) {
            if (! is_array($row)) {
                continue;
            }

            $rfqItemId = isset($row['rfq_item_id']) ? (int) $row['rfq_item_id'] : null;
            $rfqItem = $rfqItemId ? $rfqItems->get($rfqItemId) : null;

            if (! $rfqItem instanceof RfqItem) {
                continue;
            }

            $compliance = isset($row['compliance']) ? trim((string) $row['compliance']) : '';
            if ($compliance !== '' && ! in_array($compliance, QuotationCompliance::values(), true)) {
                $compliance = null;
            }

            $requestedQty = max(0, (float) $rfqItem->quantity);
            $quantityQuoted = isset($row['quantity_quoted']) && $row['quantity_quoted'] !== '' && $row['quantity_quoted'] !== null
                ? max(0, (float) $row['quantity_quoted'])
                : $requestedQty;

            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $discount = max(0, round((float) ($row['discount'] ?? 0), 2));
            $lineSubtotal = round(max(0, ($quantityQuoted * $unitPrice) - $discount), 2);

            if (isset($row['total_price']) && $row['total_price'] !== '' && $row['total_price'] !== null) {
                $totalPrice = max(0, round((float) $row['total_price'], 2));
            } else {
                $totalPrice = $lineSubtotal;
            }

            $taxRate = max(0, round((float) ($row['tax_rate'] ?? 0), 2));
            if (isset($row['tax']) && $row['tax'] !== '' && $row['tax'] !== null) {
                $tax = max(0, round((float) $row['tax'], 2));
            } elseif ($taxRate > 0) {
                $tax = round($totalPrice * ($taxRate / 100), 2);
            } else {
                $tax = 0;
            }

            $lineDelivery = max(0, round((float) ($row['delivery_charges'] ?? 0), 2));
            $lineInstallation = max(0, round((float) ($row['installation'] ?? 0), 2));

            $brand = self::nullableString($row['brand'] ?? null);
            $model = self::nullableString($row['model'] ?? null);
            $countryOfOrigin = self::nullableString($row['country_of_origin'] ?? null);
            $brandOrigin = self::nullableString($row['brand_origin'] ?? null);

            if ($brandOrigin === null && ($brand || $model || $countryOfOrigin)) {
                $brandOrigin = trim(implode(' / ', array_filter([$brand, $model, $countryOfOrigin])));
                $brandOrigin = $brandOrigin !== '' ? mb_substr($brandOrigin, 0, 255) : null;
            }

            $normalized[] = [
                'rfq_item_id' => $rfqItem->id,
                'item_number' => $rfqItem->item,
                'quantity_quoted' => $quantityQuoted > 0 ? $quantityQuoted : null,
                'compliance' => $compliance ?: null,
                'alternative_if_no' => self::nullableString($row['alternative_if_no'] ?? null),
                'item_description_if_no' => self::nullableString($row['item_description_if_no'] ?? null, 5000),
                'brand_origin' => $brandOrigin,
                'brand' => $brand,
                'model' => $model,
                'country_of_origin' => $countryOfOrigin,
                'unit_price' => $unitPrice > 0 ? $unitPrice : null,
                'currency' => self::nullableString($row['currency'] ?? null, 10),
                'total_price' => $totalPrice,
                'discount' => $discount > 0 ? $discount : null,
                'tax_rate' => $taxRate > 0 ? $taxRate : null,
                'tax' => $tax,
                'delivery_charges' => $lineDelivery > 0 ? $lineDelivery : null,
                'installation' => $lineInstallation > 0 ? $lineInstallation : null,
                'lead_time' => self::nullableString($row['lead_time'] ?? null),
                'warranty' => self::nullableString($row['warranty'] ?? null),
                'remarks' => self::nullableString($row['remarks'] ?? null, 5000),
                'line_grand' => round($totalPrice + $tax + $lineDelivery + $lineInstallation, 2),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function applyGrandTotal(array $header, array $items): array
    {
        $linesTotal = 0.0;

        foreach ($items as $row) {
            $linesTotal += (float) ($row['line_grand'] ?? 0);
        }

        $deliveryCharges = max(0, round((float) ($header['delivery_charges'] ?? 0), 2));
        $installationCharges = max(0, round((float) ($header['installation_charges'] ?? 0), 2));
        $totalDiscount = max(0, round((float) ($header['total_discount'] ?? 0), 2));

        $header['delivery_charges'] = $deliveryCharges > 0 ? $deliveryCharges : null;
        $header['installation_charges'] = $installationCharges > 0 ? $installationCharges : null;
        $header['total_discount'] = $totalDiscount > 0 ? $totalDiscount : null;
        $header['grand_total'] = round($linesTotal + $deliveryCharges + $installationCharges - $totalDiscount, 2);

        return $header;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(VendorQuotation $quotation, array $items, Rfq $rfq): void
    {
        $quotation->items()->delete();

        foreach (array_values($items) as $index => $row) {
            VendorQuotationItem::query()->create([
                'vendor_quotation_id' => $quotation->id,
                'rfq_item_id' => $row['rfq_item_id'],
                'sort_order' => $index,
                'item_number' => $row['item_number'] ?? null,
                'quantity_quoted' => $row['quantity_quoted'] ?? null,
                'compliance' => $row['compliance'] ?? null,
                'alternative_if_no' => $row['alternative_if_no'] ?? null,
                'item_description_if_no' => $row['item_description_if_no'] ?? null,
                'brand_origin' => $row['brand_origin'] ?? null,
                'brand' => $row['brand'] ?? null,
                'model' => $row['model'] ?? null,
                'country_of_origin' => $row['country_of_origin'] ?? null,
                'unit_price' => $row['unit_price'] ?? null,
                'currency' => $row['currency'] ?? null,
                'total_price' => $row['total_price'],
                'discount' => $row['discount'] ?? null,
                'tax_rate' => $row['tax_rate'] ?? null,
                'tax' => $row['tax'],
                'delivery_charges' => $row['delivery_charges'] ?? null,
                'installation' => $row['installation'] ?? null,
                'lead_time' => $row['lead_time'] ?? null,
                'warranty' => $row['warranty'] ?? null,
                'remarks' => $row['remarks'] ?? null,
            ]);
        }
    }

    private static function nullableString(mixed $value, int $max = 255): ?string
    {
        $text = trim((string) ($value ?? ''));

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, $max);
    }
}
