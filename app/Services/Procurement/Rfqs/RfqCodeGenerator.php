<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\Rfqs\Rfq;

class RfqCodeGenerator
{
    private const PREFIX = 'RFQ-';

    public function next(): string
    {
        $max = 0;
        $codes = Rfq::query()->withTrashed()->where('rfq_number', 'like', self::PREFIX.'%')->pluck('rfq_number');
        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        $candidate = $max + 1;
        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            $exists = Rfq::query()->withTrashed()->where('rfq_number', $next)->exists();
            if (! $exists) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique RFQ number.');
    }
}
