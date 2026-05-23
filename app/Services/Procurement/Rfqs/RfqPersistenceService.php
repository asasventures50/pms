<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqItem;
use Illuminate\Support\Facades\DB;

class RfqPersistenceService
{
    public function __construct(
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function create(array $header, array $items): Rfq
    {
        return DB::transaction(function () use ($header, $items) {
            $header = $this->applyTotals($header, $items);
            $rfq = Rfq::query()->create($header);
            $this->syncItems($rfq, $items);

            return $rfq->load(['items', 'vendor', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function update(Rfq $rfq, array $header, array $items): Rfq
    {
        return DB::transaction(function () use ($rfq, $header, $items) {
            $header = $this->applyTotals($header, $items);
            $rfq->fill($header);
            $rfq->save();
            $this->syncItems($rfq, $items);

            return $rfq->load(['items', 'vendor', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function applyTotals(array $header, array $items): array
    {
        $grandTotal = 0.0;

        foreach ($items as $row) {
            $grandTotal += (float) ($row['line_total'] ?? 0);
        }

        $header['grand_total'] = round($grandTotal, 2);

        $scopeTypes = $this->termsService->scopeTypesFromNormalizedItems($items);
        $general = $this->termsService->activeTextsForScopeTypes($scopeTypes);
        $custom = $this->termsService->normalizeTexts($header['terms_custom'] ?? []);
        unset($header['terms_custom']);

        $header['terms'] = $this->termsService->buildTermsPayload($general, $custom);

        return $header;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(Rfq $rfq, array $items): void
    {
        $rfq->items()->delete();

        foreach (array_values($items) as $index => $row) {
            RfqItem::query()->create([
                'rfq_id' => $rfq->id,
                'procurement_request_item_id' => $row['procurement_request_item_id'] ?? null,
                'sort_order' => $index,
                'item' => $row['item'] ?? null,
                'description' => $row['description'] ?? null,
                'quantity' => $row['quantity'],
                'unit' => $row['unit'] ?? null,
                'request_lead_time' => $row['request_lead_time'] ?? null,
                'compliance' => $row['compliance'] ?? null,
                'unit_price' => $row['unit_price'] ?? null,
                'line_total' => $row['line_total'],
                'quote_lead_time' => $row['quote_lead_time'] ?? null,
                'warranty' => $row['warranty'] ?? null,
                'line_terms' => [],
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rawItems
     * @return list<array<string, mixed>>
     */
    public static function normalizeItems(array $rawItems): array
    {
        $normalized = [];
        $prItems = ProcurementRequestItem::query()
            ->whereIn('id', collect($rawItems)->pluck('procurement_request_item_id')->filter())
            ->get()
            ->keyBy('id');

        foreach ($rawItems as $row) {
            if (! is_array($row)) {
                continue;
            }

            $prItemId = isset($row['procurement_request_item_id'])
                ? (int) $row['procurement_request_item_id']
                : null;

            if ($prItemId) {
                $prItem = $prItems->get($prItemId);
                if (! $prItem) {
                    continue;
                }

                $description = trim((string) $prItem->description);
                $quantity = max(0, (float) $prItem->quantity);
                $unit = trim((string) ($prItem->unit ?? '')) ?: null;
                $itemCode = trim((string) ($prItem->line_number ?? '')) ?: null;
                $requestLeadTime = $prItem->required_delivery_date?->format('Y-m-d');
            } else {
                $description = trim((string) ($row['description'] ?? ''));
                if ($description === '') {
                    continue;
                }

                $quantity = max(0, (float) ($row['quantity'] ?? 0));
                $unit = isset($row['unit']) ? trim((string) $row['unit']) : null;
                $itemCode = isset($row['item']) ? trim((string) $row['item']) : null;
                $requestLeadTime = isset($row['request_lead_time'])
                    ? trim((string) $row['request_lead_time'])
                    : null;
                $requestLeadTime = $requestLeadTime !== '' ? $requestLeadTime : null;
            }

            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $lineTotal = round($quantity * $unitPrice, 2);

            $normalized[] = [
                'procurement_request_item_id' => $prItemId ?: null,
                'item' => $itemCode,
                'description' => $description,
                'quantity' => $quantity,
                'unit' => $unit,
                'request_lead_time' => $requestLeadTime ?? null,
                'compliance' => isset($row['compliance']) ? trim((string) $row['compliance']) : null,
                'unit_price' => $unitPrice > 0 ? $unitPrice : null,
                'line_total' => $lineTotal,
                'quote_lead_time' => isset($row['quote_lead_time']) ? trim((string) $row['quote_lead_time']) : null,
                'warranty' => isset($row['warranty']) ? trim((string) $row['warranty']) : null,
            ];
        }

        return $normalized;
    }
}
