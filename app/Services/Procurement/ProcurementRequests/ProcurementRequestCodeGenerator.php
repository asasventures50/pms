<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;

class ProcurementRequestCodeGenerator
{
    private const PREFIX = 'PR-';

    public function next(): string
    {
        $max = 0;
        $codes = ProcurementRequest::query()
            ->withTrashed()
            ->where('request_number', 'like', self::PREFIX.'%')
            ->pluck('request_number');

        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        $candidate = $max + 1;
        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            $exists = ProcurementRequest::query()->withTrashed()->where('request_number', $next)->exists();
            if (! $exists) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique procurement request number.');
    }
}
