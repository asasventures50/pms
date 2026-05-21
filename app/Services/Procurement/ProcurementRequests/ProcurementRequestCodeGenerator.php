<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class ProcurementRequestCodeGenerator
{
    private const PREFIX = 'PR-';

    public function next(?CarbonInterface $date = null): string
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateKey = $date->format('dmY');
        $prefix = self::PREFIX.$dateKey.'-';

        $max = 0;
        $codes = ProcurementRequest::query()
            ->withTrashed()
            ->where('request_number', 'like', $prefix.'%')
            ->pluck('request_number');

        foreach ($codes as $code) {
            $pattern = '/^'.preg_quote($prefix, '/').'(\d+)$/';
            if (preg_match($pattern, (string) $code, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        $candidate = $max + 1;
        do {
            $next = $prefix.$candidate;
            $exists = ProcurementRequest::query()->withTrashed()->where('request_number', $next)->exists();
            if (! $exists) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique procurement request number.');
    }
}
