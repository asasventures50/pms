<?php

namespace App\Services\Procurement\ScheduleOfWorks;

use App\Models\Procurement\ScheduleOfWorks\ScheduleOfWork;

class ScheduleOfWorkCodeGenerator
{
    private const PREFIX = 'SOW-';

    public function next(): string
    {
        $max = 0;
        $codes = ScheduleOfWork::query()->where('document_number', 'like', self::PREFIX.'%')->pluck('document_number');

        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        $candidate = $max + 1;

        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            if (! ScheduleOfWork::query()->where('document_number', $next)->exists()) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique schedule of works number.');
    }
}
