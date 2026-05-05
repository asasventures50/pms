<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;

class PurchaseOrderCodeGenerator
{
    private const PREFIX = 'PO-';

    /**
     * Next available code in the form PO-0001, PO-0002, … (zero-padded to 4 digits).
     */
    public function next(): string
    {
        $max = 0;
        $codes = PurchaseOrder::query()->withTrashed()->where('po_number', 'like', self::PREFIX.'%')->pluck('po_number');
        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        $candidate = $max + 1;
        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            $exists = PurchaseOrder::query()->withTrashed()->where('po_number', $next)->exists();
            if (! $exists) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique PO number.');
    }
}
