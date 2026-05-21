<?php

namespace App\Services\Procurement\Projects;

use App\Models\Procurement\Projects\Project;

class ProjectCodeGenerator
{
    private const PREFIX = 'PRJ-';

    public function next(): string
    {
        $max = 0;
        $codes = Project::query()->withTrashed()->where('code', 'like', self::PREFIX.'%')->pluck('code');

        foreach ($codes as $code) {
            if (preg_match('/^'.preg_quote(self::PREFIX, '/').'(\d+)$/', (string) $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        $candidate = $max + 1;

        do {
            $next = self::PREFIX.str_pad((string) $candidate, 4, '0', STR_PAD_LEFT);
            $exists = Project::query()->withTrashed()->where('code', $next)->exists();
            if (! $exists) {
                return $next;
            }
            $candidate++;
        } while ($candidate < 1_000_000);

        throw new \RuntimeException('Could not generate a unique project code.');
    }
}
