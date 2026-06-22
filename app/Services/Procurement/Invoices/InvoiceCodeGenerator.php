<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\Invoices\Invoice;

class InvoiceCodeGenerator
{
    private const PREFIX = 'INV-';

    public function next(): string
    {
        $max = 0;
        $codes = Invoice::query()->where('invoice_number', 'like', self::PREFIX.'%')->pluck('invoice_number');

        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        $candidate = $max + 1;

        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            if (! Invoice::query()->where('invoice_number', $next)->exists()) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique invoice number.');
    }
}
