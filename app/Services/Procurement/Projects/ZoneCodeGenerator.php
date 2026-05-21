<?php

namespace App\Services\Procurement\Projects;

use App\Models\Procurement\Projects\Zone;

class ZoneCodeGenerator
{
    private const PREFIX = 'Z-';

    public function nextForProject(int $projectId): string
    {
        $max = 0;
        $codes = Zone::query()
            ->withTrashed()
            ->where('project_id', $projectId)
            ->where('code', 'like', self::PREFIX.'%')
            ->pluck('code');

        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        $candidate = $max + 1;

        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            $exists = Zone::query()
                ->withTrashed()
                ->where('project_id', $projectId)
                ->where('code', $next)
                ->exists();
            if (! $exists) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique zone code for this project.');
    }
}
