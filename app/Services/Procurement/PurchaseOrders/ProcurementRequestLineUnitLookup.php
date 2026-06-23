<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use Illuminate\Support\Collection;

class ProcurementRequestLineUnitLookup
{
    /**
     * @return array<string, string> P.R. line number => unit
     */
    public static function unitsByLineCode(?ProcurementRequest $procurementRequest): array
    {
        if ($procurementRequest === null) {
            return [];
        }

        $procurementRequest->loadMissing('items');

        $map = [];

        foreach ($procurementRequest->items as $item) {
            if (! $item instanceof ProcurementRequestItem) {
                continue;
            }

            $lineCode = trim((string) ($item->line_number ?? ''));
            $unit = trim((string) ($item->unit ?? ''));

            if ($lineCode !== '' && $unit !== '') {
                $map[$lineCode] = $unit;
            }
        }

        return $map;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function applyToPoItemRows(array $rows, array $unitsByLineCode): array
    {
        if ($unitsByLineCode === []) {
            return $rows;
        }

        return array_map(function (array $row) use ($unitsByLineCode): array {
            $resolved = self::resolve(
                isset($row['item']) ? (string) $row['item'] : null,
                isset($row['unit']) ? (string) $row['unit'] : null,
                $unitsByLineCode,
            );

            if ($resolved !== null) {
                $row['unit'] = $resolved;
            }

            return $row;
        }, $rows);
    }

    /**
     * @param  Collection<int, PurchaseOrderItem>  $purchaseOrderItems
     * @return array<string, string>
     */
    public static function unitsByLineCodeForPurchaseOrderItems(Collection $purchaseOrderItems): array
    {
        if ($purchaseOrderItems->isEmpty()) {
            return [];
        }

        $purchaseOrders = PurchaseOrder::query()
            ->with(['procurementRequest.items'])
            ->whereIn('id', $purchaseOrderItems->pluck('purchase_order_id')->unique()->all())
            ->get();

        $map = [];

        foreach ($purchaseOrders as $purchaseOrder) {
            foreach (self::unitsByLineCode($purchaseOrder->procurementRequest) as $lineCode => $unit) {
                $map[$lineCode] = $unit;
            }
        }

        return $map;
    }

    public static function resolve(?string $lineCode, ?string $unit, array $unitsByLineCode): ?string
    {
        $unit = trim((string) ($unit ?? ''));
        if ($unit !== '') {
            return $unit;
        }

        $lineCode = trim((string) ($lineCode ?? ''));
        if ($lineCode === '' || ! isset($unitsByLineCode[$lineCode])) {
            return null;
        }

        return $unitsByLineCode[$lineCode];
    }

    public static function resolveForPurchaseOrderItem(
        PurchaseOrderItem $item,
        array $unitsByLineCode,
    ): ?string {
        return self::resolve(
            isset($item->item) ? (string) $item->item : null,
            isset($item->unit) ? (string) $item->unit : null,
            $unitsByLineCode,
        );
    }
}
