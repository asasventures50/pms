<?php

namespace App\Services\Activity;

use App\Models\Activity\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ActivityLogInsights
{
    public function __construct(
        protected ActivityLogReportBuilder $reportBuilder,
    ) {}

    /**
     * @return array{
     *     total_events: int,
     *     unique_users: int,
     *     first_at: Carbon|null,
     *     last_at: Carbon|null,
     *     total_span: string|null,
     *     average_gap: string|null
     * }
     */
    public function forQuery(Builder $query): array
    {
        $base = clone $query;

        $totalEvents = (clone $base)->count();
        $uniqueUsers = (int) (clone $base)->whereNotNull('user_id')->distinct()->count('user_id');

        $bounds = (clone $base)
            ->selectRaw('min(created_at) as first_at, max(created_at) as last_at')
            ->first();

        $firstAt = $bounds?->first_at ? Carbon::parse($bounds->first_at) : null;
        $lastAt = $bounds?->last_at ? Carbon::parse($bounds->last_at) : null;

        $totalSpan = null;
        $averageGap = null;

        if ($totalEvents >= 2 && $firstAt !== null && $lastAt !== null) {
            $totalSpan = $this->reportBuilder->formatDuration(
                (int) $firstAt->diffInSeconds($lastAt, absolute: true),
            );

            if ($totalEvents <= 500) {
                $averageGap = $this->averageGapForQuery(clone $base);
            }
        }

        return [
            'total_events' => $totalEvents,
            'unique_users' => $uniqueUsers,
            'first_at' => $firstAt,
            'last_at' => $lastAt,
            'total_span' => $totalSpan,
            'average_gap' => $averageGap,
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, ActivityLog>  $logs
     * @return array<int, string>
     */
    public function gapsForPage(LengthAwarePaginator $logs): array
    {
        $items = collect($logs->items());
        $gaps = [];

        foreach ($items as $index => $log) {
            $next = $items[$index + 1] ?? null;

            if ($next === null || $log->created_at === null || $next->created_at === null) {
                continue;
            }

            $seconds = (int) $next->created_at->diffInSeconds($log->created_at, absolute: true);
            $gaps[$log->id] = $this->reportBuilder->formatDuration($seconds);
        }

        return $gaps;
    }

    public function actionTone(string $action): string
    {
        if (str_starts_with($action, 'create_')) {
            return 'create';
        }

        if (str_starts_with($action, 'update_')) {
            return 'update';
        }

        if (str_starts_with($action, 'delete_')) {
            return 'delete';
        }

        if (str_starts_with($action, 'restore_')) {
            return 'restore';
        }

        return match ($action) {
            'login' => 'login',
            'logout' => 'logout',
            default => 'default',
        };
    }

    /**
     * @param  Collection<int, ActivityLog>  $logs
     * @return Collection<string, Collection<int, ActivityLog>>
     */
    public function groupByDay(Collection $logs): Collection
    {
        return $logs->groupBy(function (ActivityLog $log) {
            return $log->created_at?->format('Y-m-d') ?? 'unknown';
        });
    }

    public function dayHeading(?Carbon $date): string
    {
        if ($date === null) {
            return 'Unknown date';
        }

        if ($date->isToday()) {
            return 'Today · '.$date->format('d M Y');
        }

        if ($date->isYesterday()) {
            return 'Yesterday · '.$date->format('d M Y');
        }

        return $date->format('l, d M Y');
    }

    private function averageGapForQuery(Builder $query): ?string
    {
        $timestamps = $query
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('created_at');

        if ($timestamps->count() < 2) {
            return null;
        }

        $gaps = [];

        for ($index = 1; $index < $timestamps->count(); $index++) {
            $previous = Carbon::parse($timestamps[$index - 1]);
            $current = Carbon::parse($timestamps[$index]);
            $gaps[] = (int) $previous->diffInSeconds($current, absolute: true);
        }

        return $this->reportBuilder->formatDuration((int) round(array_sum($gaps) / count($gaps)));
    }
}
