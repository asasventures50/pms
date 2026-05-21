<?php

namespace App\Services\Procurement\ProcurementRequests;

class ProcurementRequestLineNumberFormatter
{
    public static function format(string $requestNumber, int $zeroBasedLineIndex): string
    {
        $seq = 1;
        if (preg_match('/-(\d+)$/', $requestNumber, $matches)) {
            $seq = max(1, (int) $matches[1]);
        }

        $prPart = str_pad((string) $seq, 2, '0', STR_PAD_LEFT);

        return $prPart.'.'.($zeroBasedLineIndex + 1);
    }
}
