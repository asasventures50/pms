<?php

namespace App\Services\Procurement\Vendors;

use App\Models\Procurement\Vendors\Vendor;

class VendorCodeGenerator
{
    private const PREFIX = 'VND-';

    /**
     * Next available code in the form VND-0001, VND-0002, … (zero-padded to 4 digits).
     */
    public function next(): string
    {
        $max = 0;
        $codes = Vendor::query()->withTrashed()->where('vendor_code', 'like', self::PREFIX.'%')->pluck('vendor_code');
        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        $candidate = $max + 1;
        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            $exists = Vendor::query()->withTrashed()->where('vendor_code', $next)->exists();
            if (! $exists) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique vendor code.');
    }
}
