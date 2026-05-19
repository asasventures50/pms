<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderPersistenceService
{
    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function create(array $header, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($header, $items) {
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
        $grandTotal = 0.0;

        foreach ($items as $row) {
            $grandTotal += (float) ($row['line_total'] ?? 0);
        }

        $header['total_price'] = round($grandTotal, 2);

        if (empty($header['title'])) {
            $poNumber = $header['po_number'] ?? '';
            $header['title'] = $poNumber !== '' ? 'Purchase Order '.$poNumber : 'Purchase Order';
        }

        return $header;
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
