<?php

namespace App\Services\Procurement\QuickReceipts;

use App\Models\Procurement\QuickReceipts\QuickReceipt;

class QuickReceiptCodeGenerator
{
    private const PREFIX = 'QR-';

    public function next(): string
    {
        $max = 0;
        $codes = QuickReceipt::query()->where('code', 'like', self::PREFIX.'%')->pluck('code');

        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        $candidate = $max + 1;

        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            if (! QuickReceipt::query()->where('code', $next)->exists()) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique quick receipt code.');
    }
}
