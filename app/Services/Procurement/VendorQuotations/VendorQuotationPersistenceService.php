<?php

namespace App\Services\Procurement\VendorQuotations;

use App\Enums\Procurement\VendorQuotations\QuotationCompliance;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\VendorQuotations\VendorQuotationItem;
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

            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $quantity = max(0, (float) $rfqItem->quantity);
            $lineSubtotal = round($quantity * $unitPrice, 2);

            if (isset($row['total_price']) && $row['total_price'] !== '' && $row['total_price'] !== null) {
                $totalPrice = max(0, round((float) $row['total_price'], 2));
            } else {
                $totalPrice = $lineSubtotal;
            }

            $tax = max(0, round((float) ($row['tax'] ?? 0), 2));

            $normalized[] = [
                'rfq_item_id' => $rfqItem->id,
                'item_number' => $rfqItem->item,
                'compliance' => $compliance ?: null,
                'alternative_if_no' => self::nullableString($row['alternative_if_no'] ?? null),
                'item_description_if_no' => self::nullableString($row['item_description_if_no'] ?? null),
                'brand_origin' => self::nullableString($row['brand_origin'] ?? null),
                'unit_price' => $unitPrice > 0 ? $unitPrice : null,
                'currency' => self::nullableString($row['currency'] ?? null, 10),
                'total_price' => $totalPrice,
                'tax' => $tax,
                'lead_time' => self::nullableString($row['lead_time'] ?? null),
                'warranty' => self::nullableString($row['warranty'] ?? null),
                'line_grand' => round($totalPrice + $tax, 2),
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
        $grandTotal = 0.0;

        foreach ($items as $row) {
            $grandTotal += (float) ($row['line_grand'] ?? 0);
        }

        $header['grand_total'] = round($grandTotal, 2);

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
                'compliance' => $row['compliance'] ?? null,
                'alternative_if_no' => $row['alternative_if_no'] ?? null,
                'item_description_if_no' => $row['item_description_if_no'] ?? null,
                'brand_origin' => $row['brand_origin'] ?? null,
                'unit_price' => $row['unit_price'] ?? null,
                'currency' => $row['currency'] ?? null,
                'total_price' => $row['total_price'],
                'tax' => $row['tax'],
                'lead_time' => $row['lead_time'] ?? null,
                'warranty' => $row['warranty'] ?? null,
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
