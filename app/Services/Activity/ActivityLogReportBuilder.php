<?php

namespace App\Services\Activity;

use App\Models\Activity\ActivityLog;
use Illuminate\Support\Collection;

class ActivityLogReportBuilder
{
    /**
     * @var array<string, array{singular: string, plural: string}>
     */
    private const ENTITY_LABELS = [
        'vendor' => ['singular' => 'vendor', 'plural' => 'vendors'],
        'category' => ['singular' => 'category', 'plural' => 'categories'],
        'subcategory' => ['singular' => 'subcategory', 'plural' => 'subcategories'],
        'po' => ['singular' => 'purchase order', 'plural' => 'purchase orders'],
        'rfq' => ['singular' => 'RFQ', 'plural' => 'RFQs'],
        'rfq_general_term' => ['singular' => 'RFQ general term', 'plural' => 'RFQ general terms'],
        'vendor_quotation' => ['singular' => 'vendor quotation', 'plural' => 'vendor quotations'],
        'procurement_request' => ['singular' => 'procurement request (P.R.)', 'plural' => 'procurement requests (P.R.)'],
        'pr' => ['singular' => 'procurement request (P.R.)', 'plural' => 'procurement requests (P.R.)'],
        'invoice' => ['singular' => 'invoice', 'plural' => 'invoices'],
        'schedule_of_work' => ['singular' => 'schedule of work', 'plural' => 'schedules of work'],
        'project' => ['singular' => 'project', 'plural' => 'projects'],
        'zone' => ['singular' => 'zone', 'plural' => 'zones'],
        'country' => ['singular' => 'country', 'plural' => 'countries'],
        'city' => ['singular' => 'city', 'plural' => 'cities'],
        'quick_receipt' => ['singular' => 'quick receipt', 'plural' => 'quick receipts'],
    ];

    /**
     * @var array<string, string>
     */
    private const EVENT_VERBS = [
        'create' => 'Created',
        'update' => 'Updated',
        'delete' => 'Deleted',
        'restore' => 'Restored',
        'login' => 'Logged in',
        'logout' => 'Logged out',
    ];

    /**
     * @param  Collection<int, ActivityLog>  $logs
     * @return list<array{
     *     user_id: int|null,
     *     user_name: string,
     *     user_email: string|null,
     *     summaries: list<array{action: string, count: int, label: string}>,
     *     statistics: array{
     *         event_count: int,
     *         total_span: string|null,
     *         average_gap: string|null,
     *         longest_gap: string|null,
     *         shortest_gap: string|null
     *     }|null,
     *     timeline: list<array{
     *         when: string,
     *         label: string,
     *         gap_from_previous: string|null
     *     }>
     * }>
     */
    public function build(Collection $logs): array
    {
        return $logs
            ->groupBy(fn (ActivityLog $log) => $log->user_id ?? 'system')
            ->sortKeys()
            ->map(function (Collection $userLogs, int|string $userId) {
                $first = $userLogs->first();
                $user = $first?->user;

                $summaries = $userLogs
                    ->groupBy('action')
                    ->map(fn (Collection $actionLogs, string $action) => [
                        'action' => $action,
                        'count' => $actionLogs->count(),
                        'label' => $this->summarizeAction($action, $actionLogs->count()),
                    ])
                    ->sortBy('label')
                    ->values()
                    ->all();

                [$timeline, $statistics] = $this->buildTimelineWithStatistics($userLogs);

                return [
                    'user_id' => is_numeric($userId) ? (int) $userId : null,
                    'user_name' => $user?->name ?? 'System / unknown user',
                    'user_email' => $user?->email,
                    'summaries' => $summaries,
                    'statistics' => $statistics,
                    'timeline' => $timeline,
                ];
            })
            ->values()
            ->all();
    }

    public function summarizeAction(string $action, int $count): string
    {
        $count = max(1, $count);
        $parsed = $this->parseAction($action);

        if ($parsed['entity'] === null) {
            $verb = self::EVENT_VERBS[$parsed['event']] ?? ucfirst(str_replace('_', ' ', $parsed['event']));

            return $count === 1
                ? $verb
                : "{$verb} {$count} times";
        }

        $labels = self::ENTITY_LABELS[$parsed['entity']] ?? [
            'singular' => str_replace('_', ' ', $parsed['entity']),
            'plural' => str_replace('_', ' ', $parsed['entity']).'s',
        ];

        $verb = self::EVENT_VERBS[$parsed['event']] ?? ucfirst($parsed['event']);
        $entity = $count === 1 ? $labels['singular'] : $labels['plural'];

        if ($count === 1) {
            return "{$verb} {$entity}";
        }

        return "{$verb} {$count} {$entity}";
    }

    public function formatDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);

        if ($seconds < 60) {
            return $seconds === 1 ? '1 second' : "{$seconds} seconds";
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            if ($remainingSeconds === 0) {
                return $minutes === 1 ? '1 minute' : "{$minutes} minutes";
            }

            return "{$minutes} min {$remainingSeconds} sec";
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours < 24) {
            if ($remainingMinutes === 0) {
                return $hours === 1 ? '1 hour' : "{$hours} hours";
            }

            return "{$hours} hr {$remainingMinutes} min";
        }

        $days = intdiv($hours, 24);
        $remainingHours = $hours % 24;

        if ($remainingHours === 0) {
            return $days === 1 ? '1 day' : "{$days} days";
        }

        return "{$days} day".($days === 1 ? '' : 's')." {$remainingHours} hr";
    }

    /**
     * @return array{
     *     0: list<array{when: string, label: string, gap_from_previous: string|null}>,
     *     1: array{
     *         event_count: int,
     *         total_span: string|null,
     *         average_gap: string|null,
     *         longest_gap: string|null,
     *         shortest_gap: string|null
     *     }|null
     * }
     */
    private function buildTimelineWithStatistics(Collection $userLogs): array
    {
        $sorted = $userLogs
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        if ($sorted->isEmpty()) {
            return [[], null];
        }

        $timeline = [];
        $gapSeconds = [];
        $previous = null;

        foreach ($sorted as $log) {
            $gapLabel = null;

            if ($previous !== null && $log->created_at !== null && $previous->created_at !== null) {
                $seconds = (int) $previous->created_at->diffInSeconds($log->created_at, absolute: true);
                $gapSeconds[] = $seconds;
                $gapLabel = $this->formatDuration($seconds);
            }

            $timeline[] = [
                'when' => $log->created_at?->format('Y-m-d H:i') ?? '—',
                'label' => $log->description ?? $this->summarizeAction($log->action, 1),
                'gap_from_previous' => $gapLabel,
            ];

            $previous = $log;
        }

        return [$timeline, $this->buildStatistics($sorted, $gapSeconds)];
    }

    /**
     * @param  list<int>  $gapSeconds
     * @return array{
     *     event_count: int,
     *     total_span: string|null,
     *     average_gap: string|null,
     *     longest_gap: string|null,
     *     shortest_gap: string|null
     * }|null
     */
    private function buildStatistics(Collection $sorted, array $gapSeconds): ?array
    {
        $eventCount = $sorted->count();

        if ($eventCount === 0) {
            return null;
        }

        $first = $sorted->first()->created_at;
        $last = $sorted->last()->created_at;

        $totalSpan = null;
        if ($eventCount >= 2 && $first !== null && $last !== null) {
            $totalSpan = $this->formatDuration((int) $first->diffInSeconds($last, absolute: true));
        }

        if ($gapSeconds === []) {
            return [
                'event_count' => $eventCount,
                'total_span' => $totalSpan,
                'average_gap' => null,
                'longest_gap' => null,
                'shortest_gap' => null,
            ];
        }

        return [
            'event_count' => $eventCount,
            'total_span' => $totalSpan,
            'average_gap' => $this->formatDuration((int) round(array_sum($gapSeconds) / count($gapSeconds))),
            'longest_gap' => $this->formatDuration(max($gapSeconds)),
            'shortest_gap' => $this->formatDuration(min($gapSeconds)),
        ];
    }

    /**
     * @return array{event: string, entity: string|null}
     */
    private function parseAction(string $action): array
    {
        if (preg_match('/^(create|update|delete|restore)_(.+)$/', $action, $matches) === 1) {
            return [
                'event' => $matches[1],
                'entity' => $matches[2],
            ];
        }

        return [
            'event' => $action,
            'entity' => null,
        ];
    }
}
