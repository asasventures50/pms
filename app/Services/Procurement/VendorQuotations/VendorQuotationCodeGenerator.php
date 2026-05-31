<?php

namespace App\Services\Procurement\VendorQuotations;

use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\VendorQuotations\VendorQuotation;

class VendorQuotationCodeGenerator
{
    public function nextForRfq(Rfq $rfq): string
    {
        $prefix = 'QUO-'.$rfq->rfq_number.'-';
        $max = 0;

        $codes = VendorQuotation::query()
            ->withTrashed()
            ->where('rfq_id', $rfq->id)
            ->where('quotation_number', 'like', $prefix.'%')
            ->pluck('quotation_number');

        foreach ($codes as $code) {
            if (preg_match('/-(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        $candidate = $max + 1;

        do {
            $next = $prefix.str_pad((string) $candidate, 2, '0', STR_PAD_LEFT);
            $exists = VendorQuotation::query()
                ->withTrashed()
                ->where('quotation_number', $next)
                ->exists();

            if (! $exists) {
                return $next;
            }

            $candidate++;
        } while ($candidate < 10_000);

        throw new \RuntimeException('Could not generate a unique vendor quotation number.');
    }
}
