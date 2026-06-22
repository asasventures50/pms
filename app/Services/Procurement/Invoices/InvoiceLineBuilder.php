<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use Illuminate\Support\Collection;

class InvoiceLineBuilder
{
    /**
     * @param  Collection<int, PurchaseOrderItem>  $selectedItems
     * @param  list<array{description: string, item_ids: list<int>}>  $mergeGroups
     * @return list<array{
     *     line_number: int,
     *     description: string,
     *     quantity: float|string,
     *     unit: string|null,
     *     unit_price: float|string,
     *     line_total: float|string,
     *     source_purchase_order_item_ids: list<int>
     * }>
     */
    public function build(Collection $selectedItems, array $mergeGroups = []): array
    {
        $selectedItems = $selectedItems->values();
        $groupForItem = [];

        foreach ($mergeGroups as $group) {
            foreach ($group['item_ids'] as $itemId) {
                $groupForItem[(int) $itemId] = $group;
            }
        }

        $lines = [];
        $lineNumber = 1;
        $processed = [];

        foreach ($selectedItems as $item) {
            $itemId = (int) $item->id;

            if (in_array($itemId, $processed, true)) {
                continue;
            }

            $group = $groupForItem[$itemId] ?? null;

            if ($group !== null) {
                $groupItemIds = collect($group['item_ids'])->map(fn ($id) => (int) $id)->all();
                $groupItems = $selectedItems->whereIn('id', $groupItemIds)->values();
                $lines[] = $this->mergedLine($lineNumber++, trim($group['description']), $groupItems);
                array_push($processed, ...$groupItemIds);

                continue;
            }

            $lines[] = $this->individualLine($lineNumber++, $item);
            $processed[] = $itemId;
        }

        return $lines;
    }

    /**
     * @param  Collection<int, PurchaseOrderItem>  $groupItems
     * @return array<string, mixed>
     */
    private function mergedLine(int $lineNumber, string $description, Collection $groupItems): array
    {
        $total = round((float) $groupItems->sum('line_total'), 2);

        return [
            'line_number' => $lineNumber,
            'description' => $description,
            'quantity' => 1,
            'unit' => null,
            'unit_price' => $total,
            'line_total' => $total,
            'source_purchase_order_item_ids' => $groupItems->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function individualLine(int $lineNumber, PurchaseOrderItem $item): array
    {
        return [
            'line_number' => $lineNumber,
            'description' => trim((string) ($item->description ?? '')),
            'quantity' => $item->quantity,
            'unit' => filled($item->unit) ? trim((string) $item->unit) : null,
            'unit_price' => $item->unit_price,
            'line_total' => $item->line_total,
            'source_purchase_order_item_ids' => [(int) $item->id],
        ];
    }
}
