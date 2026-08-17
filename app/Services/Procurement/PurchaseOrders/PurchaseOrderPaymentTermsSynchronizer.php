<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderPaymentTerm;

class PurchaseOrderPaymentTermsSynchronizer
{
    /**
     * @param  mixed  $raw
     * @return list<array{id: int|null, milestone: string, percentage: float|null, amount: float|null, notes: string}>
     */
    public static function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $idRaw = $row['id'] ?? null;
            $id = is_numeric($idRaw) ? (int) $idRaw : null;
            $milestone = trim((string) ($row['milestone'] ?? ''));
            $notes = trim((string) ($row['notes'] ?? ''));
            $percentageRaw = $row['percentage'] ?? null;
            $amountRaw = $row['amount'] ?? null;

            $percentage = ($percentageRaw === null || $percentageRaw === '')
                ? null
                : round((float) $percentageRaw, 2);
            $amount = ($amountRaw === null || $amountRaw === '')
                ? null
                : round((float) $amountRaw, 2);

            if ($milestone === '' && $notes === '' && $percentage === null && $amount === null && $id === null) {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'milestone' => $milestone,
                'percentage' => $percentage,
                'amount' => $amount,
                'notes' => $notes,
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array{milestone?: string, percentage?: float|null, amount?: float|null}>  $rows
     */
    public static function flatten(array $rows): ?string
    {
        $lines = [];

        foreach ($rows as $row) {
            $parts = [];
            $milestone = trim((string) ($row['milestone'] ?? ''));
            if ($milestone !== '') {
                $parts[] = $milestone;
            }
            if (($row['percentage'] ?? null) !== null && $row['percentage'] !== '') {
                $parts[] = rtrim(rtrim(number_format((float) $row['percentage'], 2), '0'), '.').'%';
            }
            if (($row['amount'] ?? null) !== null && $row['amount'] !== '') {
                $parts[] = number_format((float) $row['amount'], 2);
            }
            $line = implode(' — ', $parts);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        if ($lines === []) {
            return null;
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<array{id: int|null, milestone: string, percentage: float|null, amount: float|null}>  $rows
     */
    public function sync(PurchaseOrder $purchaseOrder, array $rows): void
    {
        $keepIds = [];
        $sort = 0;

        foreach ($rows as $row) {
            $existing = null;
            if ($row['id'] !== null) {
                $existing = PurchaseOrderPaymentTerm::query()
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->where('id', $row['id'])
                    ->first();
            }

            if ($existing !== null) {
                if ($existing->invoice_id !== null) {
                    $existing->sort_order = $sort++;
                    $existing->save();
                    $keepIds[] = $existing->id;

                    continue;
                }

                $existing->fill([
                    'milestone' => $row['milestone'],
                    'percentage' => $row['percentage'],
                    'amount' => $row['amount'],
                    'notes' => $row['notes'] !== '' ? $row['notes'] : null,
                    'sort_order' => $sort++,
                ]);
                $existing->save();
                $keepIds[] = $existing->id;

                continue;
            }

            if ($row['milestone'] === '' && $row['notes'] === '' && $row['percentage'] === null && $row['amount'] === null) {
                continue;
            }

            $created = PurchaseOrderPaymentTerm::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'milestone' => $row['milestone'],
                'percentage' => $row['percentage'],
                'amount' => $row['amount'],
                'notes' => $row['notes'] !== '' ? $row['notes'] : null,
                'sort_order' => $sort++,
            ]);
            $keepIds[] = $created->id;
        }

        PurchaseOrderPaymentTerm::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->whereNull('invoice_id')
            ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds), fn ($q) => $q)
            ->delete();
    }
}
