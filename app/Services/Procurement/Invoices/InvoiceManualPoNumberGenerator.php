<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;

class InvoiceManualPoNumberGenerator
{
    private const PREFIX = 'PO-';

    public function next(): string
    {
        $max = 0;

        $codeSets = [
            PurchaseOrder::query()->withTrashed()->where('po_number', 'like', self::PREFIX.'%')->pluck('po_number'),
            Invoice::query()->where('source', Invoice::SOURCE_MANUAL)->where('po_number', 'like', self::PREFIX.'%')->pluck('po_number'),
        ];

        foreach ($codeSets as $codes) {
            foreach ($codes as $code) {
                if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $matches)) {
                    $max = max($max, (int) $matches[1]);
                }
            }
        }

        $candidate = $max + 1;

        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            $exists = PurchaseOrder::query()->withTrashed()->where('po_number', $next)->exists()
                || Invoice::query()->where('source', Invoice::SOURCE_MANUAL)->where('po_number', $next)->exists();

            if (! $exists) {
                return $next;
            }

            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique manual PO number.');
    }
}
