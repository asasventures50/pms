<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Enums\Procurement\PurchaseOrders\BuyerCompany;
use Illuminate\Support\Facades\DB;

class PurchaseOrderPersistenceService
{
    public function __construct(
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function create(array $header, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($header, $items) {
            BuyerCompany::applyToHeader($header);
            $header = $this->applyTotals($header, $items);
            $purchaseOrder = PurchaseOrder::query()->create($header);
            $this->syncItems($purchaseOrder, $items);

            return $purchaseOrder->load(['items', 'vendor', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function update(PurchaseOrder $purchaseOrder, array $header, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $header, $items) {
            BuyerCompany::applyToHeader($header);
            $header = $this->applyTotals($header, $items);
            $purchaseOrder->fill($header);
            $purchaseOrder->save();
            $this->syncItems($purchaseOrder, $items);

            return $purchaseOrder->load(['items', 'vendor', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function applyTotals(array $header, array $items): array
    {
        $linesSubtotal = 0.0;

        foreach ($items as $row) {
            $linesSubtotal += (float) ($row['line_total'] ?? 0);
        }

        $deliveryFee = max(0, (float) ($header['delivery_fee'] ?? 0));
        $discount = max(0, (float) ($header['discount'] ?? 0));
        $header['delivery_fee'] = round($deliveryFee, 2);
        $header['discount'] = round($discount, 2);
        $header['total_price'] = round(max(0, $linesSubtotal + $deliveryFee - $discount), 2);

        if (empty($header['title'])) {
            $poNumber = $header['po_number'] ?? '';
            $header['title'] = $poNumber !== '' ? 'Purchase Order '.$poNumber : 'Purchase Order';
        }

        $locale = $header['terms_locale'] ?? RfqTermsLocale::default()->value;
        if (! in_array($locale, RfqTermsLocale::values(), true)) {
            $locale = RfqTermsLocale::default()->value;
        }
        $header['terms_locale'] = $locale;

        $scopeTypes = $this->scopeTypesForTerms($header);
        $general = $this->termsService->activeTextsForScopeTypes($scopeTypes, $locale);
        $custom = $this->termsService->normalizeTexts($header['terms_custom'] ?? []);
        unset($header['terms_custom']);

        $header['terms'] = $this->termsService->buildTermsPayload($general, $custom);

        return $header;
    }

    /**
     * @param  array<string, mixed>  $header
     * @return list<string>
     */
    private function scopeTypesForTerms(array $header): array
    {
        $types = [];

        if (! empty($header['handover_at'])) {
            $types[] = 'Maintenance';
        }

        if (! empty($header['dismantling_at'])) {
            $types[] = 'Dismantling';
        }

        return $types;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        $purchaseOrder->items()->delete();

        foreach (array_values($items) as $index => $row) {
            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'sort_order' => $index,
                'item' => $row['item'] ?? null,
                'description' => $row['description'] ?? null,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'line_total' => $row['line_total'],
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

        foreach ($rawItems as $row) {
            if (! is_array($row)) {
                continue;
            }

            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            $quantity = max(0, (float) ($row['quantity'] ?? 0));
            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $lineTotal = round($quantity * $unitPrice, 2);

            $normalized[] = [
                'item' => isset($row['item']) ? trim((string) $row['item']) : null,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        return $normalized;
    }
}
