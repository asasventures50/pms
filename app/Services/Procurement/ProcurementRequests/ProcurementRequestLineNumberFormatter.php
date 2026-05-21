<?php

namespace App\Services\Procurement\ProcurementRequests;

class ProcurementRequestLineNumberFormatter
{
    /**
     * e.g. PR-21052026-3-03.1 (doc no. + line suffix)
     */
    public static function format(?string $requestNumber, int $zeroBasedLineIndex): ?string
    {
        $requestNumber = trim((string) ($requestNumber ?? ''));
        if ($requestNumber === '') {
            return null;
        }

        $seq = 1;
        if (preg_match('/-(\d+)$/', $requestNumber, $matches)) {
            $seq = max(1, (int) $matches[1]);
        }

        $lineSuffix = str_pad((string) $seq, 2, '0', STR_PAD_LEFT).'.'.($zeroBasedLineIndex + 1);

        return $requestNumber.'-'.$lineSuffix;
    }
}
